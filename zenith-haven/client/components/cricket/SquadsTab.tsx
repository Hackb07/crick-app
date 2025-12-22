import { Search, ArrowLeft, ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";
import { useState, useMemo } from "react";

interface Player {
  id: string;
  name: string;
  role: string;
  type: "regular" | "guest";
  isGuest: boolean;
}

interface SquadsData {
  teamA: Player[];
  teamB: Player[];
}

interface SquadsTabProps {
  squads: SquadsData;
  onSquadsChange: (squads: SquadsData) => void;
  onBack: () => void;
  onNext: () => void;
}

const AVAILABLE_PLAYERS: Player[] = [
  { id: "1", name: "Virat Kohli", role: "Right-hand Bat • Pace", type: "regular", isGuest: false },
  { id: "2", name: "Rohit Sharma", role: "Right-hand Bat • Spin", type: "regular", isGuest: false },
  { id: "3", name: "Jasprit Bumrah", role: "Right-hand Bat • Fast", type: "regular", isGuest: false },
  { id: "4", name: "Suryakumar Yadav", role: "Right-hand Bat • Pace", type: "regular", isGuest: false },
  { id: "5", name: "Hardik Pandya", role: "Right-hand Bat • Fast", type: "regular", isGuest: false },
  { id: "6", name: "KL Rahul", role: "Left-hand Bat • Spin", type: "regular", isGuest: false },
  { id: "7", name: "Guest Player 1", role: "Right-hand Bat • Pace", type: "guest", isGuest: true },
  { id: "8", name: "Guest Player 2", role: "Left-hand Bat • Fast", type: "guest", isGuest: true },
];

interface TeamTabProps {
  teamName: string;
  players: Player[];
  onPlayersChange: (players: Player[]) => void;
}

function TeamTab({ teamName, players, onPlayersChange }: TeamTabProps) {
  const [search, setSearch] = useState("");

  const filteredPlayers = useMemo(() => {
    return AVAILABLE_PLAYERS.filter(
      (p) =>
        p.name.toLowerCase().includes(search.toLowerCase()) &&
        !players.some((selected) => selected.id === p.id)
    );
  }, [search, players]);

  const handleTogglePlayer = (player: Player) => {
    const isSelected = players.some((p) => p.id === player.id);
    if (isSelected) {
      onPlayersChange(players.filter((p) => p.id !== player.id));
    } else {
      onPlayersChange([...players, player]);
    }
  };

  const selectedPlayerIds = new Set(players.map((p) => p.id));

  return (
    <div className="space-y-4">
      <div className="relative">
        <Search className="absolute left-3 top-3 text-gray-400" size={18} />
        <input
          type="text"
          placeholder="Search players..."
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className={cn(
            "w-full pl-10 pr-4 py-2.5 rounded-md border text-sm transition-colors placeholder:text-gray-400",
            "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-0 focus:border-primary",
            "bg-white text-gray-900 border-gray-300"
          )}
        />
      </div>

      <div className="space-y-2 max-h-96 overflow-y-auto">
        {filteredPlayers.length === 0 ? (
          <div className="text-center py-8">
            <p className="text-sm text-gray-500">
              {search ? "No players found" : "All players selected"}
            </p>
          </div>
        ) : (
          filteredPlayers.map((player) => (
            <button
              key={player.id}
              onClick={() => handleTogglePlayer(player)}
              className={cn(
                "w-full p-4 rounded-lg border-2 transition-all text-left",
                selectedPlayerIds.has(player.id)
                  ? "bg-blue-50 border-primary"
                  : "bg-white border-gray-200 hover:border-gray-300"
              )}
            >
              <div className="flex items-start gap-3">
                <div
                  className={cn(
                    "w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 font-semibold text-white",
                    selectedPlayerIds.has(player.id)
                      ? "bg-primary"
                      : "bg-gray-300 text-gray-600"
                  )}
                >
                  {player.name.charAt(0)}
                </div>

                <div className="flex-1 min-w-0">
                  <p className="font-medium text-sm text-gray-900 truncate">
                    {player.name}
                  </p>
                  <p className="text-xs text-gray-500 mt-0.5">{player.role}</p>
                </div>

                <div className="flex gap-2 ml-2">
                  {player.isGuest && (
                    <span
                      className={cn(
                        "text-xs font-semibold px-2 py-1 rounded-full",
                        selectedPlayerIds.has(player.id)
                          ? "bg-warning text-warning-foreground"
                          : "bg-gray-200 text-gray-600 opacity-40"
                      )}
                    >
                      Guest
                    </span>
                  )}
                </div>
              </div>
            </button>
          ))
        )}
      </div>

      <div className="text-sm text-gray-600 p-3 bg-gray-50 rounded-md border border-gray-200">
        <p className="font-medium text-gray-700">
          {players.length} / 11 players selected
        </p>
      </div>
    </div>
  );
}

export default function SquadsTab({
  squads,
  onSquadsChange,
  onBack,
  onNext,
}: SquadsTabProps) {
  const [activeTeam, setActiveTeam] = useState<"teamA" | "teamB">("teamA");

  const isValid = squads.teamA.length >= 2 && squads.teamB.length >= 2;

  return (
    <div className="w-full">
      <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div className="p-6 md:p-8 border-b border-gray-200">
          <h2 className="text-lg md:text-xl font-bold text-gray-900">
            Select Squads
          </h2>
          <p className="text-sm text-gray-500 mt-1">
            Add at least 2 players per team
          </p>
        </div>

        {/* Team Tabs */}
        <div className="flex border-b border-gray-200">
          <button
            onClick={() => setActiveTeam("teamA")}
            className={cn(
              "flex-1 px-6 py-4 text-sm font-medium transition-colors border-b-2",
              activeTeam === "teamA"
                ? "text-primary border-b-primary"
                : "text-gray-500 border-b-transparent hover:text-gray-700"
            )}
          >
            <span className="hidden sm:inline">⚪ Team A</span>
            <span className="sm:hidden">Team A</span>
            <span className="ml-2 text-xs font-normal text-gray-500">
              {squads.teamA.length}
            </span>
          </button>
          <button
            onClick={() => setActiveTeam("teamB")}
            className={cn(
              "flex-1 px-6 py-4 text-sm font-medium transition-colors border-b-2",
              activeTeam === "teamB"
                ? "text-primary border-b-primary"
                : "text-gray-500 border-b-transparent hover:text-gray-700"
            )}
          >
            <span className="hidden sm:inline">⚪ Team B</span>
            <span className="sm:hidden">Team B</span>
            <span className="ml-2 text-xs font-normal text-gray-500">
              {squads.teamB.length}
            </span>
          </button>
        </div>

        {/* Content */}
        <div className="p-6 md:p-8">
          {activeTeam === "teamA" ? (
            <TeamTab
              teamName="Team A"
              players={squads.teamA}
              onPlayersChange={(players) =>
                onSquadsChange({ ...squads, teamA: players })
              }
            />
          ) : (
            <TeamTab
              teamName="Team B"
              players={squads.teamB}
              onPlayersChange={(players) =>
                onSquadsChange({ ...squads, teamB: players })
              }
            />
          )}
        </div>

        {/* Buttons */}
        <div className="p-6 md:p-8 border-t border-gray-200 flex gap-3 justify-end">
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
            Continue
            <ArrowRight size={16} />
          </button>
        </div>
      </div>
    </div>
  );
}
