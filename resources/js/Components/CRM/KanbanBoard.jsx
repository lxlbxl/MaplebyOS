import { DragDropContext, Droppable, Draggable } from '@hello-pangea/dnd';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { cn } from '@/lib/utils';
import { DollarSign, User } from 'lucide-react';

const COLUMNS = [
    { id: 'new', title: 'New', color: 'bg-blue-500/10 text-blue-500 border-blue-500/20' },
    { id: 'contacted', title: 'Contacted', color: 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20' },
    { id: 'qualified', title: 'Qualified', color: 'bg-purple-500/10 text-purple-500 border-purple-500/20' },
    { id: 'proposal', title: 'Proposal', color: 'bg-indigo-500/10 text-indigo-500 border-indigo-500/20' },
    { id: 'negotiation', title: 'Negotiation', color: 'bg-orange-500/10 text-orange-500 border-orange-500/20' },
    { id: 'won', title: 'Won', color: 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' },
    { id: 'lost', title: 'Lost', color: 'bg-red-500/10 text-red-500 border-red-500/20' },
];

export default function KanbanBoard({ leads, onDragEnd, onEdit }) {
    return (
        <DragDropContext onDragEnd={onDragEnd}>
            <div className="flex pb-4 overflow-x-auto gap-4 h-[calc(100vh-200px)]">
                {COLUMNS.map((col) => (
                    <div key={col.id} className="flex-shrink-0 w-80 flex flex-col">
                        <div className="mb-3 flex items-center justify-between">
                            <h3 className="font-semibold text-gray-300">{col.title}</h3>
                            <Badge variant="outline" className="bg-white/5 text-gray-400">
                                {leads[col.id]?.length || 0}
                            </Badge>
                        </div>

                        <Droppable droppableId={col.id}>
                            {(provided, snapshot) => (
                                <div
                                    {...provided.droppableProps}
                                    ref={provided.innerRef}
                                    className={cn(
                                        "flex-1 rounded-lg bg-gray-900/50 border border-white/5 p-2 transition-colors",
                                        snapshot.isDraggingOver && "bg-gray-800/50 border-emerald-500/30"
                                    )}
                                >
                                    <div className="flex flex-col gap-3">
                                        {leads[col.id]?.map((lead, index) => (
                                            <Draggable key={lead.id} draggableId={String(lead.id)} index={index}>
                                                {(provided, snapshot) => (
                                                    <Card
                                                        ref={provided.innerRef}
                                                        {...provided.draggableProps}
                                                        {...provided.dragHandleProps}
                                                        className={cn(
                                                            "bg-gray-800 border-white/10 text-white cursor-pointer hover:border-emerald-500/50 transition-all",
                                                            snapshot.isDragging && "shadow-lg border-emerald-500 rotate-2 z-50"
                                                        )}
                                                        onClick={() => onEdit(lead)}
                                                    >
                                                        <CardContent className="p-3 space-y-2">
                                                            <div className="font-medium text-sm leading-tight">{lead.title}</div>
                                                            <div className="flex items-center justify-between text-xs text-gray-400">
                                                                <div className="flex items-center">
                                                                    <User className="h-3 w-3 mr-1" />
                                                                    {lead.contact_name || lead.client?.company_name || 'No Contact'}
                                                                </div>
                                                                {lead.priority && (
                                                                    <Badge variant="outline" className="text-[10px] h-5 px-1 uppercase">
                                                                        {lead.priority}
                                                                    </Badge>
                                                                )}
                                                            </div>
                                                            {lead.estimated_value && (
                                                                <div className="flex items-center text-emerald-400 text-xs font-semibold">
                                                                    <DollarSign className="h-3 w-3 mr-1" />
                                                                    {Number(lead.estimated_value).toLocaleString()}
                                                                </div>
                                                            )}
                                                        </CardContent>
                                                    </Card>
                                                )}
                                            </Draggable>
                                        ))}
                                    </div>
                                    {provided.placeholder}
                                </div>
                            )}
                        </Droppable>
                    </div>
                ))}
            </div>
        </DragDropContext>
    );
}
