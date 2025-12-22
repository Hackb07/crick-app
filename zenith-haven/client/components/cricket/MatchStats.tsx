import { cn } from "@/lib/utils";

interface PlayerStat {
  name: string;
  runs: number;
  balls: number;
  fours: number;
  sixes: number;
  strikeRate: number;
  status: "batting" | "out" | "not-out";
}

interface MatchStatsProps {
  currentPartnershipRuns: number;
  currentPartnershipBalls: number;
  batsmenStats: PlayerStat[];
  bowlersStats: PlayerStat[];
}

function StrikeRateIndicator({ strikeRate }: { strikeRate: number }) {
  let color = "text-gray-600";
  if (strikeRate > 150) color = "text-success font-bold";
  else if (strikeRate > 120) color = "text-primary font-bold";
  else if (strikeRate < 80) color = "text-warning";

  return <span className={color}>{strikeRate.toFixed(2)}</span>;
}

export default function MatchStats({
  currentPartnershipRuns,
  currentPartnershipBalls,
  batsmenStats,
  bowlersStats,
}: MatchStatsProps) {
  const partnershipStrikeRate =
    currentPartnershipBalls > 0
      ? (currentPartnershipRuns / currentPartnershipBalls) * 100
      : 0;

  return (
    <div className="space-y-6">
      {/* Current Partnership */}
      <div className="bg-white rounded-xl border border-gray-200 p-6">
        <h3 className="text-lg font-bold text-gray-900 mb-4">
          Current Partnership
        </h3>
        <div className="grid grid-cols-3 gap-4">
          <div className="p-4 bg-blue-50 rounded-lg border border-primary">
            <p className="text-xs text-gray-600 font-semibold mb-1">Runs</p>
            <p className="text-3xl font-bold text-primary">
              {currentPartnershipRuns}
            </p>
          </div>
          <div className="p-4 bg-blue-50 rounded-lg border border-primary">
            <p className="text-xs text-gray-600 font-semibold mb-1">Balls</p>
            <p className="text-3xl font-bold text-primary">
              {currentPartnershipBalls}
            </p>
          </div>
          <div className="p-4 bg-blue-50 rounded-lg border border-primary">
            <p className="text-xs text-gray-600 font-semibold mb-1">SR</p>
            <p className="text-3xl font-bold text-primary">
              {partnershipStrikeRate.toFixed(1)}
            </p>
          </div>
        </div>
      </div>

      {/* Batting Statistics */}
      {batsmenStats.length > 0 && (
        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div className="p-6 border-b border-gray-200">
            <h3 className="text-lg font-bold text-gray-900">
              Batting Statistics
            </h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="text-left px-4 py-3 font-semibold text-gray-900">
                    Batsman
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    Runs
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    Balls
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    4s
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    6s
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    SR
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    Status
                  </th>
                </tr>
              </thead>
              <tbody>
                {batsmenStats.map((player, idx) => (
                  <tr
                    key={idx}
                    className={cn(
                      "border-b border-gray-200 transition-colors",
                      player.status === "batting" ? "bg-blue-50" : "bg-white"
                    )}
                  >
                    <td className="px-4 py-3">
                      <span className="font-semibold text-gray-900">
                        {player.name}
                      </span>
                      {player.status === "batting" && (
                        <span className="ml-2 text-xs text-primary font-bold">
                          ●
                        </span>
                      )}
                    </td>
                    <td className="text-center px-4 py-3">
                      <span className="font-bold text-gray-900">
                        {player.runs}
                      </span>
                    </td>
                    <td className="text-center px-4 py-3 text-gray-600">
                      {player.balls}
                    </td>
                    <td className="text-center px-4 py-3 text-gray-600">
                      {player.fours}
                    </td>
                    <td className="text-center px-4 py-3">
                      <span className="font-bold text-primary">
                        {player.sixes}
                      </span>
                    </td>
                    <td className="text-center px-4 py-3">
                      <StrikeRateIndicator strikeRate={player.strikeRate} />
                    </td>
                    <td className="text-center px-4 py-3">
                      <span
                        className={cn(
                          "text-xs font-semibold capitalize",
                          player.status === "out"
                            ? "text-live"
                            : "text-success"
                        )}
                      >
                        {player.status === "not-out"
                          ? "Not Out"
                          : player.status}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Bowling Statistics */}
      {bowlersStats.length > 0 && (
        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
          <div className="p-6 border-b border-gray-200">
            <h3 className="text-lg font-bold text-gray-900">
              Bowling Statistics
            </h3>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th className="text-left px-4 py-3 font-semibold text-gray-900">
                    Bowler
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    Overs
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    Runs
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    Wickets
                  </th>
                  <th className="text-center px-4 py-3 font-semibold text-gray-900">
                    Economy
                  </th>
                </tr>
              </thead>
              <tbody>
                {bowlersStats.map((player, idx) => {
                  const overs = Math.floor(player.balls / 6);
                  const balls = player.balls % 6;
                  const economy =
                    overs > 0 || balls > 0
                      ? (player.runs / ((overs * 6 + balls) / 6)).toFixed(2)
                      : "0.00";

                  return (
                    <tr
                      key={idx}
                      className="border-b border-gray-200 bg-white hover:bg-gray-50 transition-colors"
                    >
                      <td className="px-4 py-3">
                        <span className="font-semibold text-gray-900">
                          {player.name}
                        </span>
                      </td>
                      <td className="text-center px-4 py-3 text-gray-600">
                        {overs}.{balls}
                      </td>
                      <td className="text-center px-4 py-3">
                        <span className="font-bold text-gray-900">
                          {player.runs}
                        </span>
                      </td>
                      <td className="text-center px-4 py-3">
                        <span className="font-bold text-live">
                          {player.sixes}
                        </span>
                      </td>
                      <td className="text-center px-4 py-3 text-gray-600">
                        {economy}
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </div>
  );
}
