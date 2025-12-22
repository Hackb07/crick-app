import { ArrowLeft, Check, X } from "lucide-react";
import { cn } from "@/lib/utils";

interface ValidationCheck {
  label: string;
  status: "passed" | "failed";
}

interface StartTabProps {
  checks: ValidationCheck[];
  onBack: () => void;
  onStartMatch: () => void;
  readyToStart: boolean;
}

export default function StartTab({
  checks,
  onBack,
  onStartMatch,
  readyToStart,
}: StartTabProps) {
  if (readyToStart) {
    return (
      <div className="w-full">
        <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div className="p-6 md:p-8 border-b border-gray-200">
            <h2 className="text-lg md:text-xl font-bold text-gray-900">
              Ready for Liftoff
            </h2>
          </div>

          {/* Content */}
          <div className="p-6 md:p-8 flex flex-col items-center justify-center text-center py-12 md:py-16">
            <span className="text-6xl md:text-7xl mb-6">🚀</span>
            <h3 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
              All Systems Go
            </h3>
            <p className="text-gray-600 mb-8 max-w-sm">
              All systems check passed. Your match is ready to begin!
            </p>

            <div className="w-full space-y-3">
              {checks.map((check, idx) => (
                <div
                  key={idx}
                  className="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg"
                >
                  <Check className="text-success flex-shrink-0" size={20} />
                  <span className="text-sm font-medium text-gray-900">
                    {check.label}
                  </span>
                </div>
              ))}
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
              onClick={onStartMatch}
              className={cn(
                "px-8 py-2.5 rounded-md text-sm font-bold transition-all",
                "bg-primary text-white hover:bg-primary/90",
                "animate-pulse-glow uppercase tracking-wide"
              )}
            >
              ▶ Start Match Now
            </button>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full">
      <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div className="p-6 md:p-8 border-b border-gray-200">
          <h2 className="text-lg md:text-xl font-bold text-gray-900">
            Pre-Flight Checks
          </h2>
        </div>

        {/* Content */}
        <div className="p-6 md:p-8 flex flex-col items-center justify-center text-center py-12 md:py-16">
          <span className="text-6xl md:text-7xl mb-6">🛑</span>
          <h3 className="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
            Pre-Flight Checks Failed
          </h3>
          <p className="text-gray-600 mb-8">
            Please complete the following items before starting
          </p>

          <div className="w-full space-y-3 max-w-md">
            {checks.map((check, idx) => (
              <div
                key={idx}
                className={cn(
                  "flex items-center gap-3 p-4 rounded-lg border",
                  check.status === "passed"
                    ? "bg-green-50 border-green-200"
                    : "bg-red-50 border-red-200"
                )}
              >
                {check.status === "passed" ? (
                  <Check className="text-success flex-shrink-0" size={20} />
                ) : (
                  <X className="text-live flex-shrink-0" size={20} />
                )}
                <span className="text-sm font-medium text-gray-900">
                  {check.label}
                </span>
              </div>
            ))}
          </div>
        </div>

        {/* Buttons */}
        <div className="p-6 md:p-8 border-t border-gray-200 flex gap-3 justify-center">
          <button
            onClick={onBack}
            className={cn(
              "flex items-center gap-2 px-6 py-2.5 rounded-md text-sm font-medium transition-all",
              "bg-white border border-gray-300 text-gray-700",
              "hover:bg-gray-50"
            )}
          >
            <ArrowLeft size={16} />
            Back to Squads
          </button>
        </div>
      </div>
    </div>
  );
}
