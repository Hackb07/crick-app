import { Menu, X } from "lucide-react";
import { cn } from "@/lib/utils";

interface ConsoleHeaderProps {
  teamA: string;
  teamB: string;
  status: "scheduled" | "live" | "completed";
  onMenuToggle?: () => void;
  onExit: () => void;
}

const statusConfig = {
  scheduled: { label: "Scheduled", color: "bg-gray-200 text-gray-900" },
  live: { label: "Live", color: "bg-live text-live-foreground" },
  completed: { label: "Completed", color: "bg-success text-success-foreground" },
};

export default function ConsoleHeader({
  teamA,
  teamB,
  status,
  onMenuToggle,
  onExit,
}: ConsoleHeaderProps) {
  const statusInfo = statusConfig[status];

  return (
    <header className="sticky top-0 z-40 w-full bg-white border-b border-gray-200 shadow-sm">
      <div className="h-14 flex items-center justify-between px-4 md:px-6">
        <div className="flex items-center gap-4 flex-1">
          {onMenuToggle && (
            <button
              onClick={onMenuToggle}
              className="p-1 hover:bg-gray-100 rounded transition-colors text-gray-500"
              aria-label="Menu"
            >
              <Menu size={20} />
            </button>
          )}
          <h1 className="text-sm md:text-base font-bold text-gray-900 hidden sm:block">
            {teamA} vs {teamB}
          </h1>
          <h1 className="text-sm font-bold text-gray-900 sm:hidden truncate">
            {teamA} vs {teamB}
          </h1>
        </div>

        <div className="flex items-center gap-3">
          <span
            className={cn(
              "text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wide",
              statusInfo.color
            )}
          >
            {statusInfo.label}
          </span>
          <button
            onClick={onExit}
            className="p-1 hover:bg-gray-100 rounded transition-colors text-gray-500"
            aria-label="Exit"
          >
            <X size={20} />
          </button>
        </div>
      </div>
    </header>
  );
}
