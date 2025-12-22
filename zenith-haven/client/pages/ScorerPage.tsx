import { useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import ConsoleHeader from "@/components/cricket/ConsoleHeader";
import { cn } from "@/lib/utils";
import { ChevronDown, ChevronUp } from "lucide-react";

interface MatchState {
  teamA: string;
  teamB: string;
}

interface BallRecord {
  id: string;
  runs: number;
  isWicket: boolean;
  extra?: string;
}

const SAMPLE_BATSMEN = ["Virat Kohli", "Rohit Sharma", "Suryakumar Yadav"];
const SAMPLE_BOWLERS = ["Jasprit Bumrah", "Yuzvendra Chahal", "Arshdeep Singh"];

export default function ScorerPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const state = (location.state || {}) as MatchState;

  const teamA = state.teamA || "Team A";
  const teamB = state.teamB || "Team B";

  // Score state
  const [runs, setRuns] = useState(0);
  const [wickets, setWickets] = useState(0);
  const [overs, setOvers] = useState(0);
  const [balls, setBalls] = useState(0);
  const [balls_data, setBallsData] = useState<BallRecord[]>([]);

  // Partnership state
  const [partnershipRuns, setPartnershipRuns] = useState(0);
  const [partnershipBalls, setPartnershipBalls] = useState(0);

  // UI state
  const [currentBatsman, setCurrentBatsman] = useState(SAMPLE_BATSMEN[0]);
  const [currentBowler, setCurrentBowler] = useState(SAMPLE_BOWLERS[0]);
  const [showExtras, setShowExtras] = useState(false);
  const [selectedExtra, setSelectedExtra] = useState<string | null>(null);

  const handleAddRuns = (runCount: number) => {
    setRuns(runs + runCount);
    setPartnershipRuns(partnershipRuns + runCount);
    updateBall(runCount, false, null);
  };

  const handleWicket = () => {
    setWickets(wickets + 1);
    updateBall(0, true, null);
  };

  const handleExtra = (extraType: string) => {
    setRuns(runs + 1);
    setPartnershipRuns(partnershipRuns + 1);
    updateBall(1, false, extraType);
    setSelectedExtra(null);
    setShowExtras(false);
  };

  const updateBall = (runCount: number, isWicket: boolean, extra: string | null) => {
    const newBall: BallRecord = {
      id: `ball-${Date.now()}`,
      runs: runCount,
      isWicket,
      extra: extra || undefined,
    };

    setBallsData([...balls_data, newBall]);

    // Update ball counter (only regular balls, not extras)
    if (!extra) {
      let newBalls = balls + 1;
      let newOvers = overs;

      if (newBalls === 6) {
        newOvers += 1;
        newBalls = 0;
      }

      setOvers(newOvers);
      setBalls(newBalls);
      setPartnershipBalls(partnershipBalls + 1);
    }
  };

  const handleUndo = () => {
    if (balls_data.length === 0) return;

    const lastBall = balls_data[balls_data.length - 1];
    setBallsData(balls_data.slice(0, -1));
    setRuns(Math.max(0, runs - lastBall.runs));
    setPartnershipRuns(Math.max(0, partnershipRuns - lastBall.runs));

    if (!lastBall.extra) {
      let newBalls = balls - 1;
      let newOvers = overs;

      if (newBalls < 0) {
        newOvers = Math.max(0, overs - 1);
        newBalls = 5;
      }

      setOvers(newOvers);
      setBalls(newBalls);
      setPartnershipBalls(Math.max(0, partnershipBalls - 1));
    }

    if (lastBall.isWicket) {
      setWickets(Math.max(0, wickets - 1));
    }
  };

  const runRate = (runs / Math.max(1, overs * 6 + balls)) * 6;

  // Bowling stats (sample data)
  const bowlingStats = SAMPLE_BOWLERS.map((name) => ({
    name,
    overs: Math.floor(Math.random() * 4),
    balls: Math.floor(Math.random() * 6),
    runs: Math.floor(Math.random() * 20),
    wickets: Math.floor(Math.random() * 2),
  }));

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <ConsoleHeader
        teamA={teamA}
        teamB={teamB}
        status="live"
        onExit={() => navigate("/")}
      />

      <main className="flex-1 overflow-auto">
        {/* Main Score Display */}
        <div className="bg-gradient-to-br from-primary to-blue-600 text-white p-4 md:p-6 sticky top-14 z-20">
          <div className="max-w-4xl mx-auto">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
              <div className="bg-white/20 backdrop-blur rounded-lg p-3 text-center">
                <p className="text-xs md:text-sm font-semibold opacity-90">RUNS</p>
                <p className="text-4xl md:text-5xl font-bold">{runs}</p>
              </div>
              <div className="bg-white/20 backdrop-blur rounded-lg p-3 text-center">
                <p className="text-xs md:text-sm font-semibold opacity-90">WICKETS</p>
                <p className="text-4xl md:text-5xl font-bold">{wickets}/11</p>
              </div>
              <div className="bg-white/20 backdrop-blur rounded-lg p-3 text-center">
                <p className="text-xs md:text-sm font-semibold opacity-90">OVERS</p>
                <p className="text-3xl md:text-4xl font-bold">
                  {overs}.{balls}
                </p>
              </div>
              <div className="bg-white/20 backdrop-blur rounded-lg p-3 text-center">
                <p className="text-xs md:text-sm font-semibold opacity-90">RUN RATE</p>
                <p className="text-3xl md:text-4xl font-bold">
                  {runRate.toFixed(1)}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div className="max-w-4xl mx-auto px-4 md:px-6 py-6 space-y-6">
          {/* Current Players */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="bg-white rounded-lg p-4 border border-gray-200">
              <p className="text-xs text-gray-500 font-semibold mb-1">BATSMAN</p>
              <p className="text-lg font-bold text-gray-900">{currentBatsman}</p>
            </div>
            <div className="bg-white rounded-lg p-4 border border-gray-200">
              <p className="text-xs text-gray-500 font-semibold mb-1">BOWLER</p>
              <p className="text-lg font-bold text-gray-900">{currentBowler}</p>
            </div>
          </div>

          {/* Run Buttons - Main Controls */}
          <div className="bg-white rounded-lg border border-gray-200 p-6">
            <p className="text-sm font-semibold text-gray-700 mb-4">
              How many runs? (Click one)
            </p>
            <div className="grid grid-cols-4 gap-2 md:grid-cols-7">
              {[0, 1, 2, 3, 4, 5, 6].map((runCount) => (
                <button
                  key={runCount}
                  onClick={() => handleAddRuns(runCount)}
                  className={cn(
                    "py-4 md:py-5 rounded-lg font-bold text-lg transition-all",
                    "bg-gray-100 text-gray-900 hover:bg-blue-100",
                    "active:scale-95"
                  )}
                >
                  {runCount}
                </button>
              ))}
            </div>
          </div>

          {/* Wicket & Extras Row */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <button
              onClick={handleWicket}
              className={cn(
                "py-4 rounded-lg font-bold text-lg transition-all",
                "bg-red-100 text-red-900 hover:bg-red-200",
                "active:scale-95"
              )}
            >
              🔴 Wicket!
            </button>

            <div>
              <button
                onClick={() => setShowExtras(!showExtras)}
                className={cn(
                  "w-full py-4 rounded-lg font-bold text-lg transition-all flex items-center justify-center gap-2",
                  showExtras
                    ? "bg-yellow-100 text-yellow-900"
                    : "bg-yellow-50 text-yellow-800 hover:bg-yellow-100",
                  "active:scale-95"
                )}
              >
                ⚠️ Extra
                {showExtras ? (
                  <ChevronUp size={20} />
                ) : (
                  <ChevronDown size={20} />
                )}
              </button>

              {showExtras && (
                <div className="grid grid-cols-2 gap-2 mt-2">
                  {["Wide", "No-Ball", "Bye", "Leg-Bye"].map((extra) => (
                    <button
                      key={extra}
                      onClick={() => handleExtra(extra)}
                      className={cn(
                        "py-3 rounded-lg font-semibold text-sm transition-all",
                        "bg-yellow-100 text-yellow-900 hover:bg-yellow-200",
                        "active:scale-95"
                      )}
                    >
                      {extra}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Partnership & Undo */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="bg-blue-50 border border-primary rounded-lg p-4 text-center">
              <p className="text-xs text-gray-600 font-semibold">Partnership</p>
              <p className="text-2xl font-bold text-primary">
                {partnershipRuns} ({partnershipBalls})
              </p>
            </div>

            <button
              onClick={handleUndo}
              disabled={balls_data.length === 0}
              className={cn(
                "py-4 rounded-lg font-bold transition-all",
                balls_data.length === 0
                  ? "bg-gray-100 text-gray-400 cursor-not-allowed"
                  : "bg-orange-100 text-orange-900 hover:bg-orange-200 active:scale-95"
              )}
            >
              ↶ Undo Last
            </button>

            <button
              onClick={() => {
                setPartnershipRuns(0);
                setPartnershipBalls(0);
              }}
              className="py-4 rounded-lg font-bold bg-gray-100 text-gray-900 hover:bg-gray-200 transition-all active:scale-95"
            >
              🔄 Reset Partner
            </button>
          </div>

          {/* Bowling Statistics */}
          <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div className="p-4 bg-gray-50 border-b border-gray-200">
              <p className="font-bold text-gray-900">Bowling Statistics</p>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className="text-left px-4 py-2 font-semibold text-gray-700">
                      Bowler
                    </th>
                    <th className="text-center px-2 py-2 font-semibold text-gray-700">
                      O
                    </th>
                    <th className="text-center px-2 py-2 font-semibold text-gray-700">
                      R
                    </th>
                    <th className="text-center px-2 py-2 font-semibold text-gray-700">
                      W
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {bowlingStats.map((bowler, idx) => {
                    const economy =
                      bowler.overs > 0
                        ? (
                            bowler.runs /
                            ((bowler.overs * 6 + bowler.balls) / 6)
                          ).toFixed(2)
                        : "0.00";

                    return (
                      <tr
                        key={idx}
                        className="border-b border-gray-200 hover:bg-gray-50"
                      >
                        <td className="px-4 py-3 font-semibold text-gray-900">
                          {bowler.name}
                        </td>
                        <td className="text-center px-2 py-3 text-gray-600">
                          {bowler.overs}.{bowler.balls}
                        </td>
                        <td className="text-center px-2 py-3 font-bold text-gray-900">
                          {bowler.runs}
                        </td>
                        <td className="text-center px-2 py-3 font-bold text-primary">
                          {bowler.wickets}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>

          {/* Recent Balls */}
          {balls_data.length > 0 && (
            <div className="bg-white rounded-lg border border-gray-200 p-4">
              <p className="font-bold text-gray-900 mb-3">Last Balls</p>
              <div className="flex flex-wrap gap-2">
                {balls_data.slice(-10).map((ball, idx) => (
                  <button
                    key={ball.id}
                    className={cn(
                      "w-10 h-10 rounded-lg font-bold text-sm transition-all",
                      ball.isWicket
                        ? "bg-red-100 text-red-900"
                        : ball.extra
                          ? "bg-yellow-100 text-yellow-900"
                          : ball.runs === 0
                            ? "bg-gray-100 text-gray-900"
                            : "bg-green-100 text-green-900"
                    )}
                  >
                    {ball.isWicket ? "W" : ball.extra ? "E" : ball.runs}
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Bottom CTA */}
          <div className="flex gap-3">
            <button
              onClick={() => navigate("/admin/console")}
              className="flex-1 py-3 bg-gray-100 text-gray-900 font-bold rounded-lg hover:bg-gray-200 transition-all"
            >
              ← Back
            </button>
            <button
              onClick={() => {
                if (confirm("Are you sure? This will end the match.")) {
                  navigate("/");
                }
              }}
              className="flex-1 py-3 bg-success text-white font-bold rounded-lg hover:bg-success/90 transition-all"
            >
              End Match ✓
            </button>
          </div>
        </div>
      </main>
    </div>
  );
}
