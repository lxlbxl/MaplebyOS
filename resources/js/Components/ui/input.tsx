import * as React from "react"

import { cn } from "@/lib/utils"

const Input = React.forwardRef<HTMLInputElement, React.ComponentProps<"input">>(
  ({ className, type, ...props }, ref) => {
    return (
      <input
        type={type}
        className={cn(
          "flex h-11 w-full rounded-xl border border-linen bg-cream px-4 py-2 text-sm text-charcoal placeholder:text-pebble transition-all duration-300",
          "focus:outline-none focus:bg-white focus:border-sage-light focus:ring-2 focus:ring-sage-soft",
          "hover:border-sand",
          "disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-linen",
          "file:border-0 file:bg-transparent file:text-sm file:font-medium",
          className
        )}
        ref={ref}
        {...props}
      />
    )
  }
)
Input.displayName = "Input"

export { Input }
