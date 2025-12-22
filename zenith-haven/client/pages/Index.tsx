import { useNavigate } from "react-router-dom";
import { ArrowRight, Zap, Shield, Users, BarChart3 } from "lucide-react";

export default function Index() {
  const navigate = useNavigate();

  const features = [
    {
      icon: Users,
      title: "Squad Management",
      description:
        "Easily manage player squads for both teams with an intuitive selection interface.",
    },
    {
      icon: Zap,
      title: "Match Setup",
      description:
        "Quick and seamless match configuration with date, time, venue, and format options.",
    },
    {
      icon: Shield,
      title: "Toss Management",
      description:
        "Record toss outcomes and batting decisions with visual confirmation.",
    },
    {
      icon: BarChart3,
      title: "Live Scoring",
      description:
        "Real-time match updates and scoring once the match begins.",
    },
  ];

  const stats = [
    { value: "1000+", label: "Active Matches" },
    { value: "50K+", label: "Players" },
    { value: "99.9%", label: "Uptime" },
    { value: "24/7", label: "Support" },
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
      {/* Header */}
      <header className="sticky top-0 z-40 w-full bg-white/80 backdrop-blur border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 md:px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-10 h-10 rounded-lg bg-primary flex items-center justify-center">
              <span className="text-xl text-white font-bold">🏏</span>
            </div>
            <h1 className="text-lg md:text-xl font-bold text-gray-900">
              Cricket Match Admin
            </h1>
          </div>
          <button
            onClick={() => navigate("/admin/console")}
            className="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary/90 transition-all"
          >
            New Match
            <ArrowRight size={16} />
          </button>
        </div>
      </header>

      {/* Hero Section */}
      <section className="max-w-7xl mx-auto px-4 md:px-6 py-16 md:py-24">
        <div className="text-center max-w-3xl mx-auto mb-12">
          <h2 className="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
            Manage Cricket Matches
            <span className="text-primary"> Effortlessly</span>
          </h2>
          <p className="text-lg md:text-xl text-gray-600 mb-8">
            A modern, intuitive admin dashboard for setting up and managing cricket matches. 
            From squad selection to live scoring, everything you need in one place.
          </p>
          <button
            onClick={() => navigate("/admin/console")}
            className="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white text-base font-semibold rounded-lg hover:bg-primary/90 transition-all shadow-lg hover:shadow-xl"
          >
            Get Started
            <ArrowRight size={20} />
          </button>
        </div>

        {/* Demo Card */}
        <div className="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm mb-12">
          <div className="aspect-video bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center">
            <div className="text-center">
              <div className="text-6xl mb-4">🏏</div>
              <p className="text-gray-600 font-medium">
                Cricket Match Admin Dashboard Preview
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="bg-white py-16 md:py-24">
        <div className="max-w-7xl mx-auto px-4 md:px-6">
          <div className="text-center mb-16">
            <h3 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Powerful Features
            </h3>
            <p className="text-lg text-gray-600">
              Everything you need to run professional cricket matches
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {features.map((feature, idx) => {
              const Icon = feature.icon;
              return (
                <div
                  key={idx}
                  className="p-6 rounded-xl border border-gray-200 bg-white hover:shadow-lg transition-shadow"
                >
                  <div className="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                    <Icon className="text-primary" size={24} />
                  </div>
                  <h4 className="text-lg font-semibold text-gray-900 mb-2">
                    {feature.title}
                  </h4>
                  <p className="text-gray-600 text-sm">{feature.description}</p>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="bg-gradient-to-br from-primary to-blue-600 text-white py-16 md:py-24">
        <div className="max-w-7xl mx-auto px-4 md:px-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat, idx) => (
              <div key={idx} className="text-center">
                <div className="text-3xl md:text-4xl font-bold mb-2">
                  {stat.value}
                </div>
                <div className="text-blue-100">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="max-w-7xl mx-auto px-4 md:px-6 py-16 md:py-24">
        <div className="bg-white rounded-2xl border border-gray-200 p-8 md:p-12 text-center">
          <h3 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Ready to create your first match?
          </h3>
          <p className="text-lg text-gray-600 mb-8">
            Set up a new cricket match in minutes with our intuitive setup wizard.
          </p>
          <button
            onClick={() => navigate("/admin/console")}
            className="inline-flex items-center gap-2 px-8 py-4 bg-primary text-white text-base font-semibold rounded-lg hover:bg-primary/90 transition-all"
          >
            Create New Match
            <ArrowRight size={20} />
          </button>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-gray-200 bg-white py-8 md:py-12">
        <div className="max-w-7xl mx-auto px-4 md:px-6">
          <div className="flex flex-col md:flex-row justify-between items-center gap-8">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                <span className="text-lg text-white font-bold">🏏</span>
              </div>
              <span className="font-semibold text-gray-900">
                Cricket Match Admin
              </span>
            </div>
            <p className="text-sm text-gray-600">
              © 2024 Cricket Match Admin. All rights reserved.
            </p>
            <div className="flex gap-6">
              <a href="#" className="text-sm text-gray-600 hover:text-primary">
                Privacy
              </a>
              <a href="#" className="text-sm text-gray-600 hover:text-primary">
                Terms
              </a>
              <a href="#" className="text-sm text-gray-600 hover:text-primary">
                Support
              </a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}
