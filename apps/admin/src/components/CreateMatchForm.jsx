import { useState, useEffect } from 'react';
import './CreateMatchForm.css';

function CreateMatchForm({ onMatchCreated, onCancel }) {
    const [formData, setFormData] = useState({
        team1_id: '',
        team2_id: '',
        series_id: '',
        match_date: '',
        match_time: '',
        venue: '',
        overs_per_innings: 20,
        match_type: 't20'
    });

    const [options, setOptions] = useState({
        teams: [],
        series: [],
        venues: []
    });

    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        // Fetch dropdown options
        fetch('/api/v1/options.php?type=all')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setOptions({
                        teams: data.data.teams || [],
                        series: data.data.series || [],
                        venues: data.data.venues || []
                    });
                } else {
                    setError('Failed to load options');
                }
                setLoading(false);
            })
            .catch(err => {
                setError(err.message);
                setLoading(false);
            });
    }, []);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        setSubmitting(true);
        setError(null);

        // Combine date and time
        const dateTime = `${formData.match_date} ${formData.match_time}`;

        const payload = {
            team1_id: formData.team1_id,
            team2_id: formData.team2_id,
            series_id: formData.series_id,
            match_date: dateTime,
            venue: formData.venue,
            overs_per_innings: formData.overs_per_innings,
            match_type: formData.match_type
        };

        fetch('/api/v1/matches.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                setSubmitting(false);
                if (data.success) {
                    if (onMatchCreated) onMatchCreated();
                } else {
                    setError(data.error || 'Failed to create match');
                }
            })
            .catch(err => {
                setSubmitting(false);
                setError(err.message);
            });
    };

    if (loading) return <div>Loading form...</div>;

    return (
        <div className="create-match-form">
            <h3>Create New Match</h3>
            {error && <div className="error-message">{error}</div>}

            <form onSubmit={handleSubmit}>
                <div className="form-grid">
                    <div className="form-group">
                        <label htmlFor="series_id">Series</label>
                        <select id="series_id" name="series_id" value={formData.series_id} onChange={handleChange} required>
                            <option value="">Select Series</option>
                            {options.series.map(s => (
                                <option key={s.series_id} value={s.series_id}>{s.name}</option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="venue">Venue</label>
                        <input id="venue"
                            type="text"
                            name="venue"
                            value={formData.venue}
                            onChange={handleChange}
                            list="venues-list"
                            placeholder="Enter Stadium Name"
                            required
                        />
                        <datalist id="venues-list">
                            {options.venues.map((v, i) => <option key={i} value={v} />)}
                        </datalist>
                    </div>

                    <div className="form-group">
                        <label htmlFor="team1_id">Team 1 (Home)</label>
                        <select id="team1_id" name="team1_id" value={formData.team1_id} onChange={handleChange} required>
                            <option value="">Select Team</option>
                            {options.teams.map(t => (
                                <option key={t.team_id} value={t.team_id}>{t.name}</option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="team2_id">Team 2 (Away)</label>
                        <select id="team2_id" name="team2_id" value={formData.team2_id} onChange={handleChange} required>
                            <option value="">Select Team</option>
                            {options.teams.map(t => (
                                <option key={t.team_id} value={t.team_id} disabled={t.team_id === formData.team1_id}>
                                    {t.name}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="form-group">
                        <label htmlFor="match_date">Date</label>
                        <input id="match_date" type="date" name="match_date" value={formData.match_date} onChange={handleChange} required />
                    </div>

                    <div className="form-group">
                        <label htmlFor="match_time">Time</label>
                        <input id="match_time" type="time" name="match_time" value={formData.match_time} onChange={handleChange} required />
                    </div>

                    <div className="form-group">
                        <label htmlFor="overs_per_innings">Overs / Innings</label>
                        <input id="overs_per_innings" type="number" name="overs_per_innings" value={formData.overs_per_innings} onChange={handleChange} min="1" max="90" />
                    </div>
                </div>

                <div className="form-actions">
                    <button type="button" className="btn-secondary" onClick={onCancel}>Cancel</button>
                    <button type="submit" className="btn-primary" disabled={submitting}>
                        {submitting ? 'Creating...' : 'Create Match'}
                    </button>
                </div>
            </form>
        </div>
    );
}

export default CreateMatchForm;
