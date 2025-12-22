import { ArrowLeft, ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";

interface TossData {
  winner: "teamA" | "teamB" | null;
  decision: "bat" | "bowl" | null;
}

interface TossTabProps {
  teamA: string;
  teamB: string;
  toss: TossData;
  onTossChange: (toss: TossData) => void;
  onBack: () => void;
  onNext: () => void;
}

interface SelectionCardProps {
  selected: boolean;
  onClick: () => void;
  emoji: string;
  label: string;
}

function SelectionCard({ selected, onClick, emoji, label }: SelectionCardProps) {
  return (
    <button
      onClick={onClick}
      className={cn(
        "p-6 md:p-8 rounded-2xl border-2 transition-all min-h-32 flex flex-col items-center justify-center gap-3",
        selected
          ? "border-primary bg-blue-50"
          : "border-gray-200 bg-white hover:border-gray-300"
      )}
    >
      <span className="text-4xl md:text-5xl">{emoji}</span>
      <span className="text-sm md:text-base font-semibold text-gray-900">
        {label}
      </span>
    </button>
  );
}

export default function TossTab({
  teamA,
  teamB,
  toss,
  onTossChange,
  onBack,
  onNext,
}: TossTabProps) {
  const isValid = toss.winner && toss.decision;

  return (
    <div className="w-full">
      <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div className="p-6 md:p-8 border-b border-gray-200">
          <h2 className="text-lg md:text-xl font-bold text-gray-900">
            Toss Details
          </h2>
          <p className="text-sm text-gray-500 mt-1">
            Record the match toss outcome
          </p>
        </div>

        {/* Content */}
        <div className="p-6 md:p-8 space-y-12">
          {/* Toss Winner Section */}
          <div>
            <h3 className="text-base md:text-lg font-semibold text-gray-900 mb-4">
              Who won the toss?
            </h3>
            <div className="grid grid-cols-2 gap-4">
              <SelectionCard
                selected={toss.winner === "teamA"}
                onClick={() =>
                  onTossChange({ ...toss, winner: "teamA" })
                }
                emoji="🪙"
                label={teamA}
              />
              <SelectionCard
                selected={toss.winner === "teamB"}
                onClick={() =>
                  onTossChange({ ...toss, winner: "teamB" })
                }
                emoji="🪙"
                label={teamB}
              />
            </div>
          </div>

          {/* Decision Section */}
          <div>
            <h3 className="text-base md:text-lg font-semibold text-gray-900 mb-4">
              What was their decision?
            </h3>
            <div className="grid grid-cols-2 gap-4">
              <SelectionCard
                selected={toss.decision === "bat"}
                onClick={() =>
                  onTossChange({ ...toss, decision: "bat" })
                }
                emoji="🏏"
                label="Bat First"
              />
              <SelectionCard
                selected={toss.decision === "bowl"}
                onClick={() =>
                  onTossChange({ ...toss, decision: "bowl" })
                }
                emoji="🥎"
                label="Bowl First"
              />
            </div>
          </div>
        </div>

        {/* Buttons */}
        <div className="p-6 md:p-8 border-t border-gray-200 flex gap-3 justify-between">
          <button
            onClick={onBack}
            className={cn(
              "flex items-center gap-2 px-6 py-2.5 rounded-md text-sm font-medium transition-all",
              "bg-white border border-gray-300 text-gray-700",
              "hover:bg-gray-50"
            )}
          >
            <ArrowLeft size={16} />
            Back
          </button>
          <button
            onClick={onNext}
            disabled={!isValid}
            className={cn(
              "flex items-center gap-2 px-6 py-2.5 rounded-md text-sm font-medium transition-all",
              isValid
                ? "bg-primary text-white hover:bg-primary/90"
                : "bg-gray-200 text-gray-400 cursor-not-allowed"
            )}
          >
            Confirm & Continue
            <ArrowRight size={16} />
          </button>
        </div>
      </div>
    </div>
  );
}
