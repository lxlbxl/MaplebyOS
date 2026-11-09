<?php

namespace Tests\Feature;

use App\Support\Access;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Operations\Entities\ClearingOfficer;
use Modules\Operations\Entities\ClearingWork;
use Modules\Operations\Entities\Container;
use Modules\Operations\Entities\ContainerDeposit;
use Modules\Operations\Entities\DepositRefund;
use Modules\Operations\Entities\Shipment;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Container deposits with separately tracked refunds, and clearing officers
 * with reassignment, credit that stays put, overrides and an audit trail.
 */
class DepositsAndClearingOfficersTest extends TestCase
{
    use RefreshDatabase;

    private function carContainer(string $number = 'MSCU1234565', string $status = Container::STATUS_ARRIVED): Container
    {
        return Container::create(['container_number' => $number, 'status' => $status, 'capacity' => 4]);
    }

    private function clearingJob(): Shipment
    {
        $this->post(route('operations.clearing.store'), ['cargo_description' => 'Ceramic tiles', 'containers' => 'TGHU1234567'])->assertSessionHasNoErrors();

        return Shipment::generalCargo()->latest('id')->first();
    }

    // ---- deposits ------------------------------------------------------

    public function test_puncher_records_and_chases_a_refund_and_oversight_confirms_it(): void
    {
        Carbon::setTestNow('2026-11-20 10:00:00');
        $puncher = $this->staff(Access::ROLE_PUNCHER);
        $hob = $this->staff(Access::ROLE_BUSINESS);
        $container = $this->carContainer();

        $this->actingAs($puncher)->post(route('operations.deposits.store'), [
            'container_number' => 'mscu 123456 5', 'amount' => 750000, 'paid_on' => '2026-11-18', 'payment_reference' => 'MSC-RCPT-881',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $deposit = ContainerDeposit::sole();
        $refund = $deposit->refund;
        $this->assertSame($container->id, $deposit->container_id);
        $this->assertSame('NGN', $deposit->currency);
        $this->assertSame(DepositRefund::STATUS_AWAITING_EMPTY, $refund->status);
        $this->assertSame($puncher->id, $refund->owner_id);

        // One deposit per container.
        $this->post(route('operations.deposits.store'), ['container_number' => 'MSCU1234565', 'amount' => 1000])->assertSessionHasErrors('container_number');

        // Empty returned: the refund is now claimable and due a chase today.
        $this->put(route('operations.deposits.refund', $deposit), ['empty_returned_on' => '2026-11-20'])->assertSessionHasNoErrors();
        $refund->refresh();
        $this->assertSame('2026-11-20', $container->fresh()->empty_returned_on->toDateString());
        $this->assertSame(DepositRefund::STATUS_TO_CLAIM, $refund->status);
        $this->assertSame('2026-11-20', $refund->next_follow_up_on->toDateString());

        // Claim filed: chase again in two weeks by default.
        $this->put(route('operations.deposits.refund', $deposit), ['claim_submitted_on' => '2026-11-20', 'claim_reference' => 'CLM-55'])->assertSessionHasNoErrors();
        $refund->refresh();
        $this->assertSame(DepositRefund::STATUS_CLAIMED, $refund->status);
        $this->assertSame('2026-12-04', $refund->next_follow_up_on->toDateString());

        // A follow-up moves the next chase date.
        $this->post(route('operations.deposits.follow-ups.store', $deposit), ['channel' => 'call', 'outcome' => 'Refund desk says next week', 'next_follow_up_on' => '2026-11-27'])
            ->assertSessionHasNoErrors();
        $this->assertSame('2026-11-27', $refund->fresh()->next_follow_up_on->toDateString());
        $this->assertSame(1, $refund->followUps()->count());

        // Deductions need a reason; a part refund, then the rest.
        $this->put(route('operations.deposits.refund', $deposit), ['deductions' => 50000])->assertSessionHasErrors('deduction_reason');
        $this->put(route('operations.deposits.refund', $deposit), ['deductions' => 50000, 'deduction_reason' => '2 days demurrage', 'amount_received' => 300000, 'received_on' => '2026-11-20'])
            ->assertSessionHasNoErrors();
        $this->assertSame(DepositRefund::STATUS_PART_RECEIVED, $refund->fresh()->status);
        $this->assertSame(400000.0, $deposit->fresh()->outstanding());

        $this->put(route('operations.deposits.refund', $deposit), ['amount_received' => 700000, 'received_on' => '2026-11-20'])->assertSessionHasNoErrors();
        $this->assertSame(DepositRefund::STATUS_RECEIVED, $refund->fresh()->status);
        $this->assertSame(0.0, $deposit->fresh()->outstanding());

        // Punchers cannot confirm; the Head of Business can.
        $this->post(route('operations.deposits.review', $deposit), ['action' => 'verify'])->assertForbidden();
        $this->actingAs($hob)->post(route('operations.deposits.review', $deposit), ['action' => 'verify'])->assertSessionHasNoErrors();
        $refund->refresh();
        $this->assertSame(DepositRefund::STATUS_VERIFIED, $refund->status);
        $this->assertSame($hob->id, $refund->verified_by);

        // Once confirmed, the puncher can no longer change it.
        $this->actingAs($puncher)->put(route('operations.deposits.refund', $deposit), ['amount_received' => 1])->assertForbidden();

        // Every step is on the audit trail with who did it.
        $events = Activity::where('log_name', 'deposits')->where('subject_id', $deposit->id)->orderBy('id')->pluck('event')->all();
        $this->assertSame(['created', 'updated', 'updated', 'followed_up', 'updated', 'updated', 'verify'], $events);
        $received = Activity::where('log_name', 'deposits')->where('event', 'updated')->latest('id')->first();
        $this->assertSame($puncher->id, $received->causer_id);
        $this->assertEquals(700000, $received->properties['changes']['amount_received']['new']);

        $this->actingAs($hob)->get(route('operations.deposits.show', $deposit))->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Operations/Deposits/Show')->has('audit', 7));
        Carbon::setTestNow();
    }

    public function test_clearing_job_empty_return_makes_its_refund_claimable(): void
    {
        $this->actingAs($this->staff(Access::ROLE_CEO));
        $job = $this->clearingJob();
        $container = $job->cargoContainers->first();

        $this->post(route('operations.deposits.store'), ['container_id' => $container->id, 'amount' => 500000])->assertSessionHasNoErrors();
        $this->patch(route('operations.clearing.update-status', $job), ['status' => 'empty_returned', 'date' => today()->toDateString()]);

        $this->assertSame(DepositRefund::STATUS_TO_CLAIM, ContainerDeposit::sole()->refund->status);
        $this->get(route('operations.clearing.show', $job))->assertInertia(fn (Assert $p) => $p->has('deposits.deposits', 1)->has('clearing.officers'));
    }

    public function test_write_off_needs_a_reason_and_reopen_recalculates(): void
    {
        $this->actingAs($this->staff(Access::ROLE_CEO));
        $container = $this->carContainer();
        $this->post(route('operations.deposits.store'), ['container_id' => $container->id, 'amount' => 200000]);
        $deposit = ContainerDeposit::sole();

        $this->post(route('operations.deposits.review', $deposit), ['action' => 'write_off'])->assertSessionHasErrors('reason');
        $this->post(route('operations.deposits.review', $deposit), ['action' => 'write_off', 'reason' => 'Line closed its Lagos office'])->assertSessionHasNoErrors();
        $this->assertSame(DepositRefund::STATUS_WRITTEN_OFF, $deposit->refund->fresh()->status);
        $this->assertSame(0.0, $deposit->fresh()->outstanding());

        $this->post(route('operations.deposits.review', $deposit), ['action' => 'reopen', 'reason' => 'They paid after all'])->assertSessionHasNoErrors();
        $this->assertSame(DepositRefund::STATUS_AWAITING_EMPTY, $deposit->refund->fresh()->status);
        $this->assertSame('Line closed its Lagos office', Activity::where('event', 'write_off')->sole()->properties['reason']);
    }

    public function test_who_sees_deposits(): void
    {
        $container = $this->carContainer();
        ContainerDeposit::create(['container_id' => $container->id, 'amount' => 100000]);

        $this->actingAs($this->staff(Access::ROLE_LOGISTICS))->get(route('operations.deposits.index'))->assertForbidden();
        $this->get(route('operations.containers.show', $container))->assertInertia(fn (Assert $p) => $p->where('deposits', null));

        $this->actingAs($this->staff(Access::ROLE_BUSINESS))->get(route('operations.deposits.index'))->assertOk();
        // Oversight, not data entry.
        $this->post(route('operations.deposits.store'), ['container_id' => $container->id, 'amount' => 1])->assertForbidden();

        $puncher = $this->staff(Access::ROLE_PUNCHER);
        $this->actingAs($puncher)->get(route('operations.deposits.index'))->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Operations/Deposits/Index')->where('deposits.total', 1));
        $this->get(route('dashboard'))->assertInertia(fn (Assert $p) => $p->has('data.deposits.due'));
    }

    // ---- clearing officers ---------------------------------------------

    public function test_reassignment_keeps_earlier_work_with_the_first_officer(): void
    {
        $logistics = $this->staff(Access::ROLE_LOGISTICS);
        $this->actingAs($logistics);
        $ada = ClearingOfficer::create(['name' => 'Ada Okafor']);
        $bayo = ClearingOfficer::create(['name' => 'Bayo Lawal']);
        $container = $this->carContainer('MSCU1234565', Container::STATUS_SAILED);

        // Work before anyone is assigned is not credited.
        $this->patch(route('operations.containers.update-status', $container), ['status' => 'arrived'])->assertSessionHasNoErrors();
        $this->assertSame(0, ClearingWork::count());

        $this->post(route('operations.clearing-officers.assign'), ['clearable_type' => 'container', 'clearable_id' => $container->id, 'clearing_officer_id' => $ada->id])
            ->assertSessionHasNoErrors();
        $this->patch(route('operations.containers.update-status', $container), ['status' => 'offloaded'])->assertSessionHasNoErrors();
        $this->post(route('operations.clearing-work.store'), ['clearable_type' => 'container', 'clearable_id' => $container->id, 'description' => 'Collected delivery order'])
            ->assertSessionHasNoErrors();

        // Reassigning needs a reason, and only oversight may move credit.
        $this->post(route('operations.clearing-officers.assign'), ['clearable_type' => 'container', 'clearable_id' => $container->id, 'clearing_officer_id' => $bayo->id])
            ->assertSessionHasErrors('reason');
        $this->post(route('operations.clearing-officers.assign'), [
            'clearable_type' => 'container', 'clearable_id' => $container->id, 'clearing_officer_id' => $bayo->id, 'reason' => 'Ada on leave',
            'move_earlier_work' => true, 'override_reason' => 'x',
        ])->assertForbidden();
        $this->post(route('operations.clearing-officers.assign'), [
            'clearable_type' => 'container', 'clearable_id' => $container->id, 'clearing_officer_id' => $bayo->id, 'reason' => 'Ada on leave',
        ])->assertSessionHasNoErrors();

        $this->patch(route('operations.containers.update-status', $container), ['status' => 'released'])->assertSessionHasNoErrors();

        $this->assertSame(2, $ada->work()->count());
        $this->assertSame(1, $bayo->work()->count());
        $this->assertSame('Container MSCU1234565: Released', $bayo->work()->sole()->description);

        $trail = Activity::where('log_name', 'clearing')->where('subject_id', $container->id)->orderBy('id')->get();
        $this->assertSame(['assigned', 'reassigned'], $trail->pluck('event')->all());
        $this->assertSame('Reassigned container MSCU1234565 from Ada Okafor to Bayo Lawal', $trail->last()->description);
        $this->assertSame('Ada on leave', $trail->last()->properties['reason']);
        $this->assertSame($logistics->id, $trail->last()->causer_id);

        $this->get(route('operations.containers.show', $container))->assertInertia(fn (Assert $p) => $p
            ->where('clearing.current.clearing_officer_id', $bayo->id)
            ->has('clearing.assignments', 2)->has('clearing.work', 3)->has('clearing.audit', 2));
        $this->get(route('operations.clearing-officers.show', $ada))->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Operations/ClearingOfficers/Show')->where('counts.work', 2));
    }

    public function test_override_moves_credit_but_keeps_the_original_and_logs_it(): void
    {
        $hob = $this->staff(Access::ROLE_BUSINESS);
        $ada = ClearingOfficer::create(['name' => 'Ada Okafor']);
        $bayo = ClearingOfficer::create(['name' => 'Bayo Lawal']);
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $job = $this->clearingJob();
        $this->actingAs($hob);

        $this->post(route('operations.clearing-officers.assign'), ['clearable_type' => 'job', 'clearable_id' => $job->id, 'clearing_officer_id' => $ada->id]);
        $job->transitionTo(Shipment::STATUS_ARRIVED);
        $job->transitionTo(Shipment::STATUS_DUTY_ASSESSED);
        // Assigning through one of the job's containers lands on the job.
        $this->post(route('operations.clearing-officers.assign'), [
            'clearable_type' => 'container', 'clearable_id' => $job->cargoContainers->first()->id, 'clearing_officer_id' => $bayo->id,
            'reason' => 'Ada moved to Onne', 'move_earlier_work' => true, 'override_reason' => 'Bayo actually did the assessment',
        ])->assertSessionHasNoErrors();

        $work = ClearingWork::where('clearable_id', $job->id)->get();
        $this->assertCount(2, $work);
        $this->assertTrue($work->every(fn ($w) => $w->clearing_officer_id === $bayo->id && $w->original_officer_id === $ada->id));
        $this->assertTrue($work->every(fn ($w) => $w->overridden_by === $hob->id && $w->override_reason === 'Bayo actually did the assessment'));

        $events = Activity::where('log_name', 'clearing')->where('subject_type', $job->getMorphClass())->where('subject_id', $job->id)->orderBy('id')->pluck('event')->all();
        $this->assertSame(['assigned', 'reassigned', 'credit_overridden'], $events);

        // Moving one item back by hand is also an override on the trail.
        $this->post(route('operations.clearing-work.override'), ['work_ids' => [$work->first()->id], 'clearing_officer_id' => $ada->id])->assertSessionHasErrors('reason');
        $this->post(route('operations.clearing-work.override'), ['work_ids' => [$work->first()->id], 'clearing_officer_id' => $ada->id, 'reason' => 'Arrival was Ada'])
            ->assertSessionHasNoErrors();
        $this->assertSame($ada->id, $work->first()->fresh()->clearing_officer_id);
        $this->assertSame(2, Activity::where('event', 'credit_overridden')->count());

        // Paperwork recorded on the job is credited to the current officer.
        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $this->put(route('operations.clearing.update', $job), ['paar_number' => 'PAAR-2026-778'])->assertSessionHasNoErrors();
        $this->assertSame('Recorded PAAR PAAR-2026-778', $bayo->work()->where('action', 'paperwork')->sole()->description);

        $this->actingAs($hob)->get(route('operations.clearing-officers.index'))->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Operations/ClearingOfficers/Index')->has('officers', 2)->has('recentChanges', 3));
    }

    public function test_who_can_assign_and_override(): void
    {
        $officer = ClearingOfficer::create(['name' => 'Ada Okafor']);
        $container = $this->carContainer();
        $assign = ['clearable_type' => 'container', 'clearable_id' => $container->id, 'clearing_officer_id' => $officer->id];

        $this->actingAs($this->staff(Access::ROLE_PUNCHER));
        $this->get(route('operations.clearing-officers.index'))->assertOk();
        $this->post(route('operations.clearing-officers.assign'), $assign)->assertForbidden();
        $this->post(route('operations.clearing-officers.store'), ['name' => 'Someone'])->assertForbidden();

        $this->actingAs($this->staff(Access::ROLE_LOGISTICS));
        $this->post(route('operations.clearing-officers.store'), ['name' => 'Chidi Eze', 'phone' => '0803 000 0000'])->assertSessionHasNoErrors();
        $this->post(route('operations.clearing-officers.assign'), $assign)->assertSessionHasNoErrors();
        $work = app(\Modules\Operations\Services\ClearingLedger::class)->record($container, 'note', 'Booked examination');
        $this->post(route('operations.clearing-work.override'), ['work_ids' => [$work->id], 'clearing_officer_id' => ClearingOfficer::where('name', 'Chidi Eze')->value('id'), 'reason' => 'x'])
            ->assertForbidden();
    }
}
