import { useState, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import ConsoleHeader from "@/components/cricket/ConsoleHeader";
import TabNavigation from "@/components/cricket/TabNavigation";
import ProgressBar from "@/components/cricket/ProgressBar";
import BasicsTab from "@/components/cricket/BasicsTab";
import SquadsTab from "@/components/cricket/SquadsTab";
import TossTab from "@/components/cricket/TossTab";
import StartTab from "@/components/cricket/StartTab";

interface Player {
  id: string;
  name: string;
  role: string;
  type: "regular" | "guest";
  isGuest: boolean;
}

interface BasicsFormData {
  series: string;
  venue: string;
  date: string;
  time: string;
  overs: string;
}

interface TossData {
  winner: "teamA" | "teamB" | null;
  decision: "bat" | "bowl" | null;
}

interface SquadsData {
  teamA: Player[];
  teamB: Player[];
}

const TEAM_A = "Mumbai Indians";
const TEAM_B = "Chennai Super Kings";

export default function MatchAdminConsole() {
  const navigate = useNavigate();
  const [currentTab, setCurrentTab] = useState("basics");
  const [matchStarted, setMatchStarted] = useState(false);

  const [basics, setBasics] = useState<BasicsFormData>({
    series: "",
    venue: "",
    date: "",
    time: "",
    overs: "",
  });

  const [squads, setSquads] = useState<SquadsData>({
    teamA: [],
    teamB: [],
  });

  const [toss, setToss] = useState<TossData>({
    winner: null,
    decision: null,
  });

  const tabs = [
    { id: "basics", label: "Basics", number: 1 },
    { id: "squads", label: "Squads", number: 2 },
    { id: "toss", label: "Toss", number: 3 },
    { id: "start", label: "Start", number: 4 },
  ];

  const tabStatuses = useMemo(() => {
    const isBasicsValid = basics.series && basics.venue && basics.date && basics.time && basics.overs;
    const isSquadsValid = squads.teamA.length >= 2 && squads.teamB.length >= 2;
    const isTossValid = toss.winner && toss.decision;

    return {
      basics: isBasicsValid ? "completed" : currentTab === "basics" ? "active" : "pending",
      squads: isSquadsValid ? "completed" : currentTab === "squads" ? "active" : "pending",
      toss: isTossValid ? "completed" : currentTab === "toss" ? "active" : "pending",
      start: currentTab === "start" ? "active" : "pending",
    };
  }, [basics, squads, toss, currentTab]);

  const displayTabs = tabs.map((tab) => ({
    ...tab,
    status: tabStatuses[tab.id as keyof typeof tabStatuses] as
      | "pending"
      | "active"
      | "completed",
  }));

  const progress = useMemo(() => {
    const tabOrder = ["basics", "squads", "toss", "start"];
    const currentIndex = tabOrder.indexOf(currentTab);
    return ((currentIndex + 1) / tabOrder.length) * 100;
  }, [currentTab]);

  const validationChecks = [
    {
      label: "Squads (Min 2 players/team)",
      status: squads.teamA.length >= 2 && squads.teamB.length >= 2 ? ("passed" as const) : ("failed" as const),
    },
    {
      label: "Toss Recorded",
      status: toss.winner && toss.decision ? ("passed" as const) : ("failed" as const),
    },
    {
      label: "Match Details",
      status: basics.series && basics.venue ? ("passed" as const) : ("failed" as const),
    },
  ];

  const readyToStart = validationChecks.every((c) => c.status === "passed");

  const handleTabChange = (tabId: string) => {
    setCurrentTab(tabId);
  };

  const handleStartMatch = () => {
    setMatchStarted(true);
    setTimeout(() => {
      navigate("/scorer", {
        state: {
          teamA: TEAM_A,
          teamB: TEAM_B,
          basics,
          squads,
          toss,
        },
      });
    }, 1500);
  };

  const handleExit = () => {
    if (confirm("Are you sure you want to exit? Any unsaved changes will be lost.")) {
      navigate("/");
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <ConsoleHeader
        teamA={TEAM_A}
        teamB={TEAM_B}
        status="scheduled"
        onExit={handleExit}
      />

      <ProgressBar progress={progress} />

      <TabNavigation
        tabs={displayTabs}
        onTabChange={handleTabChange}
        showScoring={matchStarted}
      />

      <main className="flex-1 overflow-auto">
        <div className="max-w-5xl mx-auto px-4 md:px-6 py-8 md:py-12">
          {currentTab === "basics" && (
            <BasicsTab
              formData={basics}
              onFormChange={setBasics}
              onBack={() => {}}
              onNext={() => setCurrentTab("squads")}
              isValid={
                basics.series && basics.venue && basics.date && basics.time && basics.overs ? true : false
              }
            />
          )}

          {currentTab === "squads" && (
            <SquadsTab
              squads={squads}
              onSquadsChange={setSquads}
              onBack={() => setCurrentTab("basics")}
              onNext={() => setCurrentTab("toss")}
            />
          )}

          {currentTab === "toss" && (
            <TossTab
              teamA={TEAM_A}
              teamB={TEAM_B}
              toss={toss}
              onTossChange={setToss}
              onBack={() => setCurrentTab("squads")}
              onNext={() => setCurrentTab("start")}
            />
          )}

          {currentTab === "start" && (
            <StartTab
              checks={validationChecks}
              onBack={() => setCurrentTab("toss")}
              onStartMatch={handleStartMatch}
              readyToStart={readyToStart}
            />
          )}
        </div>
      </main>
    </div>
  );
}
