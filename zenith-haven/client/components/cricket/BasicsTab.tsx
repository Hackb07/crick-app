import { ArrowRight, ArrowLeft } from "lucide-react";
import { cn } from "@/lib/utils";

interface BasicsFormData {
  series: string;
  venue: string;
  date: string;
  time: string;
  overs: string;
}

interface BasicsTabProps {
  formData: BasicsFormData;
  onFormChange: (data: BasicsFormData) => void;
  onBack: () => void;
  onNext: () => void;
  isValid: boolean;
}

export default function BasicsTab({
  formData,
  onFormChange,
  onBack,
  onNext,
  isValid,
}: BasicsTabProps) {
  const handleChange = (field: keyof BasicsFormData, value: string) => {
    onFormChange({ ...formData, [field]: value });
  };

  return (
    <div className="w-full">
      <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div className="p-6 md:p-8 border-b border-gray-200">
          <h2 className="text-lg md:text-xl font-bold text-gray-900">
            Match Details
          </h2>
          <p className="text-sm text-gray-500 mt-1">
            Set up the basic match information
          </p>
        </div>

        <div className="p-6 md:p-8">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* Series Dropdown */}
            <div className="flex flex-col">
              <label className="text-sm font-semibold text-gray-700 mb-2">
                Series
              </label>
              <select
                value={formData.series}
                onChange={(e) => handleChange("series", e.target.value)}
                className={cn(
                  "px-3 py-2.5 rounded-md border text-sm transition-colors",
                  "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-0 focus:border-primary",
                  "hover:border-gray-300",
                  "bg-white text-gray-900",
                  "border-gray-300"
                )}
              >
                <option value="">Select a series</option>
                <option value="ipl">IPL 2024</option>
                <option value="t20wc">T20 World Cup</option>
                <option value="bilateral">Bilateral Series</option>
                <option value="domestic">Domestic Cricket</option>
              </select>
            </div>

            {/* Date Picker */}
            <div className="flex flex-col">
              <label className="text-sm font-semibold text-gray-700 mb-2">
                Match Date
              </label>
              <input
                type="date"
                value={formData.date}
                onChange={(e) => handleChange("date", e.target.value)}
                className={cn(
                  "px-3 py-2.5 rounded-md border text-sm transition-colors",
                  "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-0 focus:border-primary",
                  "hover:border-gray-300",
                  "bg-white text-gray-900",
                  "border-gray-300"
                )}
              />
            </div>

            {/* Venue Input */}
            <div className="flex flex-col">
              <label className="text-sm font-semibold text-gray-700 mb-2">
                Venue
              </label>
              <input
                type="text"
                placeholder="Enter venue name"
                value={formData.venue}
                onChange={(e) => handleChange("venue", e.target.value)}
                className={cn(
                  "px-3 py-2.5 rounded-md border text-sm transition-colors placeholder:text-gray-400",
                  "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-0 focus:border-primary",
                  "hover:border-gray-300",
                  "bg-white text-gray-900",
                  "border-gray-300"
                )}
              />
            </div>

            {/* Time Picker */}
            <div className="flex flex-col">
              <label className="text-sm font-semibold text-gray-700 mb-2">
                Match Time
              </label>
              <input
                type="time"
                value={formData.time}
                onChange={(e) => handleChange("time", e.target.value)}
                className={cn(
                  "px-3 py-2.5 rounded-md border text-sm transition-colors",
                  "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-0 focus:border-primary",
                  "hover:border-gray-300",
                  "bg-white text-gray-900",
                  "border-gray-300"
                )}
              />
            </div>

            {/* Overs Input */}
            <div className="flex flex-col md:col-span-2">
              <label className="text-sm font-semibold text-gray-700 mb-2">
                Match Format (Overs)
              </label>
              <input
                type="number"
                placeholder="e.g., 20"
                value={formData.overs}
                onChange={(e) => handleChange("overs", e.target.value)}
                className={cn(
                  "px-3 py-2.5 rounded-md border text-sm transition-colors placeholder:text-gray-400",
                  "focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-0 focus:border-primary",
                  "hover:border-gray-300",
                  "bg-white text-gray-900",
                  "border-gray-300"
                )}
              />
            </div>
          </div>
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
            Save & Continue
            <ArrowRight size={16} />
          </button>
        </div>
      </div>
    </div>
  );
}
