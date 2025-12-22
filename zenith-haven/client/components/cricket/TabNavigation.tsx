import { Check } from "lucide-react";
import { cn } from "@/lib/utils";

interface Tab {
  id: string;
  label: string;
  number: number;
  status: "pending" | "active" | "completed";
}

interface TabNavigationProps {
  tabs: Tab[];
  onTabChange: (tabId: string) => void;
  showScoring?: boolean;
}

export default function TabNavigation({
  tabs,
  onTabChange,
  showScoring = false,
}: TabNavigationProps) {
  return (
    <nav className="sticky top-14 z-30 w-full bg-white border-b border-gray-200">
      <div className="flex items-center gap-1 md:gap-2 px-4 md:px-6 overflow-x-auto">
        {tabs.map((tab) => (
          <button
            key={tab.id}
            onClick={() => onTabChange(tab.id)}
            className={cn(
              "flex items-center gap-1.5 md:gap-2 px-3 md:px-4 py-3 md:py-4 text-xs md:text-sm font-medium whitespace-nowrap transition-colors border-b-2",
              tab.status === "active"
                ? "text-primary border-b-primary"
                : tab.status === "completed"
                  ? "text-success border-b-transparent"
                  : "text-gray-500 border-b-transparent hover:text-gray-700"
            )}
          >
            <span
              className={cn(
                "flex items-center justify-center w-5 h-5 rounded-full text-xs font-semibold",
                tab.status === "active"
                  ? "bg-primary text-white"
                  : tab.status === "completed"
                    ? "bg-success text-white"
                    : "bg-gray-200 text-gray-600"
              )}
            >
              {tab.status === "completed" ? <Check size={14} /> : tab.number}
            </span>
            <span>{tab.label}</span>
          </button>
        ))}

        {showScoring && (
          <button
            disabled
            className="flex items-center gap-1.5 md:gap-2 px-3 md:px-4 py-3 md:py-4 text-xs md:text-sm font-medium text-live whitespace-nowrap"
          >
            <span className="flex items-center justify-center w-2 h-2 rounded-full bg-live animate-ping-live" />
            <span>Scoring</span>
          </button>
        )}
      </div>
    </nav>
  );
}
