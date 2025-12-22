import { Plus, Minus, RotateCcw } from "lucide-react";
import { cn } from "@/lib/utils";
import { useState } from "react";

export interface BallRecord {
  id: string;
  over: number;
  ball: number;
  runs: number;
  isWicket: boolean;
  extra?: "wide" | "no-ball" | "bye" | "leg-bye";
  batsman: string;
  bowler: string;
  notes?: string;
}

interface BallByBallEntryProps {
  onAddBall: (ball: Omit<BallRecord, "id">) => void;
  currentOver: number;
  currentBall: number;
  batsmanName: string;
  bowlerName: string;
}

export default function BallByBallEntry({
  onAddBall,
  currentOver,
  currentBall,
  batsmanName,
  bowlerName,
}: BallByBallEntryProps) {
  const [selectedRuns, setSelectedRuns] = useState(0);
  const [isWicket, setIsWicket] = useState(false);
  const [extraType, setExtraType] = useState<
    "wide" | "no-ball" | "bye" | "leg-bye" | null
  >(null);
  const [notes, setNotes] = useState("");

  const handleAddBall = () => {
    onAddBall({
      over: currentOver,
      ball: currentBall,
      runs: selectedRuns,
      isWicket,
      extra: extraType || undefined,
      batsman: batsmanName,
      bowler: bowlerName,
      notes: notes || undefined,
    });

    // Reset form
    setSelectedRuns(0);
    setIsWicket(false);
    setExtraType(null);
    setNotes("");
  };

  const runOptions = [0, 1, 2, 3, 4, 5, 6];

  return (
    <div className="bg-white rounded-xl border border-gray-200 p-6">
      <h3 className="text-lg font-bold text-gray-900 mb-6">Record Ball</h3>

      <div className="space-y-6">
        {/* Current Ball Info */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-blue-50 rounded-lg border border-primary">
          <div>
            <p className="text-xs text-gray-600">Over</p>
            <p className="text-2xl font-bold text-primary">
              {currentOver}.{currentBall}
            </p>
          </div>
          <div>
            <p className="text-xs text-gray-600">Batsman</p>
            <p className="font-semibold text-gray-900 truncate">
              {batsmanName}
            </p>
          </div>
          <div>
            <p className="text-xs text-gray-600">Bowler</p>
            <p className="font-semibold text-gray-900 truncate">
              {bowlerName}
            </p>
          </div>
          <div>
            <p className="text-xs text-gray-600">Ball Status</p>
            <p
              className={cn(
                "font-semibold",
                isWicket ? "text-live" : "text-success"
              )}
            >
              {isWicket ? "Wicket" : "Dot/Run"}
            </p>
          </div>
        </div>

        {/* Runs Selection */}
        <div>
          <label className="text-sm font-semibold text-gray-700 mb-3 block">
            Runs Scored
          </label>
          <div className="grid grid-cols-4 md:grid-cols-7 gap-2">
            {runOptions.map((run) => (
              <button
                key={run}
                onClick={() => setSelectedRuns(run)}
                disabled={isWicket && run > 0}
                className={cn(
                  "py-3 rounded-lg border-2 font-bold text-sm transition-all",
                  selectedRuns === run
                    ? "bg-primary text-white border-primary"
                    : "bg-white text-gray-900 border-gray-300 hover:border-gray-400",
                  isWicket && run > 0
                    ? "opacity-40 cursor-not-allowed"
                    : ""
                )}
              >
                {run}
              </button>
            ))}
          </div>
        </div>

        {/* Wicket Toggle */}
        <div className="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
          <input
            type="checkbox"
            id="wicket"
            checked={isWicket}
            onChange={(e) => {
              setIsWicket(e.target.checked);
              if (e.target.checked) setSelectedRuns(0);
            }}
            className="w-5 h-5 cursor-pointer"
          />
          <label htmlFor="wicket" className="text-sm font-medium text-gray-900">
            Wicket Fell
          </label>
        </div>

        {/* Extras */}
        <div>
          <label className="text-sm font-semibold text-gray-700 mb-3 block">
            Extra Type (if any)
          </label>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-2">
            {["wide", "no-ball", "bye", "leg-bye"].map((extra) => (
              <button
                key={extra}
                onClick={() =>
                  setExtraType(
                    extraType === extra
                      ? null
                      : (extra as "wide" | "no-ball" | "bye" | "leg-bye")
                  )
                }
                className={cn(
                  "py-2 px-3 rounded-lg border-2 font-semibold text-xs transition-all capitalize",
                  extraType === extra
                    ? "bg-warning text-warning-foreground border-warning"
                    : "bg-white text-gray-900 border-gray-300 hover:border-gray-400"
                )}
              >
                {extra}
              </button>
            ))}
          </div>
        </div>

        {/* Notes */}
        <div>
          <label className="text-sm font-semibold text-gray-700 mb-2 block">
            Notes (optional)
          </label>
          <textarea
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            placeholder="Add any notes about this ball..."
            rows={2}
            className={cn(
              "w-full px-3 py-2.5 rounded-md border text-sm transition-colors placeholder:text-gray-400",
              "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-0 focus:border-primary",
              "bg-white text-gray-900 border-gray-300"
            )}
          />
        </div>

        {/* Action Buttons */}
        <div className="flex gap-3 pt-4 border-t border-gray-200">
          <button
            onClick={() => {
              setSelectedRuns(0);
              setIsWicket(false);
              setExtraType(null);
              setNotes("");
            }}
            className={cn(
              "flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-sm font-medium transition-all",
              "bg-gray-100 text-gray-700 hover:bg-gray-200"
            )}
          >
            <RotateCcw size={16} />
            Reset
          </button>
          <button
            onClick={handleAddBall}
            className={cn(
              "flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-md text-sm font-bold transition-all",
              "bg-primary text-white hover:bg-primary/90"
            )}
          >
            <Plus size={16} />
            Record Ball
          </button>
        </div>
      </div>
    </div>
  );
}
