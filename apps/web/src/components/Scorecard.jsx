import { useState } from 'react'

function Scorecard({ data, match }) {
    // data is inningsData from API
    const [activeInnings, setActiveInnings] = useState(1)

    const inn1 = data[1]
    const inn2 = data[2]

    const getTeamName = (teamId) => {
        return teamId === match.team1_id ? match.team1_name : match.team2_name
    }

    const team1Name = getTeamName(inn1?.batting_team_id || match.team1_id)
    const team2Name = getTeamName(inn2?.batting_team_id || match.team2_id)

    const renderInnings = (innData) => {
        if (!innData || (!innData.total_runs && !innData.balls_legal && (!innData.batting || Object.keys(innData.batting).length === 0))) {
            return (
                <div className="glass-card" style={{ padding: 40, textAlign: 'center', color: 'var(--text-muted)' }}>
                    Innings not started
                </div>
            )
        }

        return (
            <div className="glass-card">
                <div className="table-responsive">
                    <table className="scorecard-table">
                        <thead>
                            <tr>
                                <th>Batter</th>
                                <th style={{ textAlign: 'right' }}>R</th>
                                <th style={{ textAlign: 'right' }}>B</th>
                                <th style={{ textAlign: 'right' }}>4s</th>
                                <th style={{ textAlign: 'right' }}>6s</th>
                                <th style={{ textAlign: 'right' }}>SR</th>
                            </tr>
                        </thead>
                        <tbody>
                            {Object.values(innData.batting || {}).map((batter, index) => (
                                <tr key={index}>
                                    <td>
                                        <div className="font-bold text-sm" style={{ color: 'var(--text-main)' }}>{batter.name || 'Unknown'}</div>
                                        <div className="text-xs text-muted" style={{ marginTop: 2 }}>{batter.out ? batter.dismissal.replace(/_/g, ' ') : 'not out'}</div>
                                    </td>
                                    <td className="font-bold text-main" style={{ textAlign: 'right' }}>{batter.runs}</td>
                                    <td style={{ textAlign: 'right', color: 'var(--text-main)' }}>{batter.balls}</td>
                                    <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{batter['4s']}</td>
                                    <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{batter['6s']}</td>
                                    <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>
                                        {batter.balls > 0 ? ((batter.runs / batter.balls) * 100).toFixed(1) : '-'}
                                    </td>
                                </tr>
                            ))}
                            <tr style={{ background: '#f8fafc' }}>
                                <td className="font-bold text-muted">Extras</td>
                                <td colSpan="5" className="text-muted text-sm" style={{ textAlign: 'right' }}>
                                    {innData.extras.total} (w {innData.extras.wide}, nb {innData.extras.no_ball}, b {innData.extras.bye}, lb {innData.extras.leg_bye})
                                </td>
                            </tr>
                            <tr style={{ background: '#f1f5f9', borderTop: '2px solid #e2e8f0' }}>
                                <td className="font-bold text-main">Total</td>
                                <td colSpan="5" className="font-bold text-main" style={{ textAlign: 'right', fontSize: 16 }}>
                                    {innData.total_runs}/{innData.total_wickets}
                                    <span style={{ fontWeight: 400, marginLeft: 8, fontSize: 13, color: 'var(--text-muted)' }}>
                                        ({Math.floor(innData.balls_legal / 6)}.{innData.balls_legal % 6} ov)
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {innData.bowling && Object.keys(innData.bowling).length > 0 && (
                    <div style={{ marginTop: 20 }}>
                        <div className="card-header" style={{ background: 'transparent', borderBottom: '1px solid #e2e8f0', padding: '10px 16px' }}>
                            Bowling
                        </div>
                        <div className="table-responsive">
                            <table className="scorecard-table">
                                <thead>
                                    <tr>
                                        <th>Bowler</th>
                                        <th style={{ textAlign: 'right' }}>O</th>
                                        <th style={{ textAlign: 'right' }}>M</th>
                                        <th style={{ textAlign: 'right' }}>R</th>
                                        <th style={{ textAlign: 'right' }}>W</th>
                                        <th style={{ textAlign: 'right' }}>Econ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {Object.values(innData.bowling).map((bowler, index) => {
                                        const overs = Math.floor(bowler.balls / 6) + '.' + (bowler.balls % 6);
                                        const econ = bowler.balls > 0 ? ((bowler.runs / bowler.balls) * 6).toFixed(2) : '0.00';
                                        return (
                                            <tr key={index}>
                                                <td className="text-sm font-bold">{bowler.name || 'Unknown'}</td>
                                                <td style={{ textAlign: 'right' }}>{overs}</td>
                                                <td style={{ textAlign: 'right' }}>{bowler.maidens}</td>
                                                <td style={{ textAlign: 'right' }} className="font-bold">{bowler.runs}</td>
                                                <td style={{ textAlign: 'right' }} className="font-bold text-danger">{bowler.wickets}</td>
                                                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{econ}</td>
                                            </tr>
                                        )
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        )
    }

    return (
        <div className="scorecard-container">
            <div className="nav-tabs-scroll" style={{ background: 'transparent', borderBottom: 'none', marginBottom: 12 }}>
                <button
                    className={`nav-tab-link ${activeInnings === 1 ? 'active' : ''}`}
                    onClick={() => setActiveInnings(1)}
                    style={{ fontSize: 13, padding: '8px 16px', borderRadius: 20, background: activeInnings === 1 ? 'white' : 'transparent', borderBottom: 'none', boxShadow: activeInnings === 1 ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}
                >
                    {team1Name}
                </button>
                <button
                    className={`nav-tab-link ${activeInnings === 2 ? 'active' : ''}`}
                    onClick={() => setActiveInnings(2)}
                    style={{ fontSize: 13, padding: '8px 16px', borderRadius: 20, background: activeInnings === 2 ? 'white' : 'transparent', borderBottom: 'none', boxShadow: activeInnings === 2 ? '0 1px 3px rgba(0,0,0,0.1)' : 'none' }}
                >
                    {team2Name}
                </button>
            </div>

            <div className="innings-content">
                {activeInnings === 1 ? renderInnings(inn1) : renderInnings(inn2)}
            </div>
        </div>
    )
}

export default Scorecard
