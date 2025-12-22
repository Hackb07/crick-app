import { cn } from "@/lib/utils";

interface ScorecardDisplayProps {
  teamName: string;
  runs: number;
  wickets: number;
  overs: number;
  balls: number;
  currentRunRate: number;
  requiredRunRate?: number;
  isCurrentInnings: boolean;
}

export default function ScorecardDisplay({
  teamName,
  runs,
  wickets,
  overs,
  balls,
  currentRunRate,
  requiredRunRate,
  isCurrentInnings,
}: ScorecardDisplayProps) {
  const totalBalls = overs * 6 + balls;
  const ballsRemaining = 120 - totalBalls;
  const oversRemaining = Math.floor(ballsRemaining / 6);
  const ballsInCurrentOver = ballsRemaining % 6;

  return (
    <div
      className={cn(
        "p-6 rounded-xl border-2 transition-all",
        isCurrentInnings
          ? "bg-blue-50 border-primary"
          : "bg-white border-gray-200"
      )}
    >
      <div className="flex items-start justify-between mb-6">
        <div>
          <h3 className="text-lg font-bold text-gray-900">{teamName}</h3>
          <p className="text-xs text-gray-500 mt-1">
            {isCurrentInnings ? "Batting" : "Completed"}
          </p>
        </div>
        {isCurrentInnings && (
          <span className="px-3 py-1 bg-primary text-white text-xs font-semibold rounded-full">
            Live
          </span>
        )}
      </div>

      <div className="grid grid-cols-2 gap-4 mb-6">
        <div className="bg-white rounded-lg p-4 border border-gray-200">
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
            Runs
          </p>
          <p className="text-3xl font-bold text-gray-900">{runs}</p>
        </div>

        <div className="bg-white rounded-lg p-4 border border-gray-200">
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
            Wickets
          </p>
          <p className="text-3xl font-bold text-live">{wickets}/11</p>
        </div>

        <div className="bg-white rounded-lg p-4 border border-gray-200">
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
            Overs
          </p>
          <p className="text-2xl font-bold text-gray-900">
            {overs}.{balls}
          </p>
        </div>

        <div className="bg-white rounded-lg p-4 border border-gray-200">
          <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
            Run Rate
          </p>
          <p className="text-2xl font-bold text-primary">
            {currentRunRate.toFixed(2)}
          </p>
        </div>
      </div>

      {isCurrentInnings && (
        <div className="space-y-3 pt-4 border-t border-gray-200">
          <div className="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p className="text-xs text-gray-500">Balls Remaining</p>
              <p className="font-semibold text-gray-900">{ballsRemaining}</p>
            </div>
            <div>
              <p className="text-xs text-gray-500">Overs Remaining</p>
              <p className="font-semibold text-gray-900">
                {oversRemaining}.{ballsInCurrentOver}
              </p>
            </div>
          </div>

          {requiredRunRate !== undefined && (
            <div className="bg-orange-50 border border-orange-200 rounded-lg p-3">
              <p className="text-xs text-gray-600">Required Run Rate</p>
              <p className="text-lg font-bold text-orange-600">
                {requiredRunRate.toFixed(2)}
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
