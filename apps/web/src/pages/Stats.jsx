import { useState, useEffect } from 'react'
import BottomNav from '../components/BottomNav'

function Stats() {
    const [category, setCategory] = useState({ main: 'batting', sub: 'runs' })
    const [leaders, setLeaders] = useState([])
    const [loading, setLoading] = useState(true)

    // Sub-Categories Map
    const subCategories = {
        batting: [
            { key: 'runs', label: 'Most Runs' },
            { key: 'sixes', label: 'Most Sixes' },
            { key: 'fours', label: 'Most Fours' },
            { key: 'thirties', label: 'Most 30s' },
            { key: 'twenties', label: 'Most 20s' },
            { key: 'tens', label: 'Most 10s' }
        ],
        bowling: [
            { key: 'wickets', label: 'Most Wickets' },
            { key: 'economy', label: 'Best Economy' }
        ],
        fielding: [
            { key: 'catches', label: 'Most Catches' },
            { key: 'runouts', label: 'Most Run Outs' },
            { key: 'stumpings', label: 'Most Stumpings' }
        ]
    }

    useEffect(() => {
        setLoading(true)
        fetch(`/api/v1/leaderboard.php?category=${category.sub}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setLeaders(data.data)
                }
                setLoading(false)
            })
            .catch(err => {
                console.error(err)
                setLoading(false)
            })
    }, [category.sub])

    const handleMainCatChange = (main) => {
        // Find first sub cat
        const sub = subCategories[main][0].key
        setCategory({ main, sub })
    }

    const renderTableHeaders = () => {
        const sub = category.sub
        if (sub === 'runs') {
            return <>
                <th style={{ textAlign: 'right' }}>Runs</th>
                <th style={{ textAlign: 'right' }}>Mat</th>
                <th style={{ textAlign: 'right' }}>SR</th>
                <th style={{ textAlign: 'right' }}>4s</th>
                <th style={{ textAlign: 'right' }}>6s</th>
            </>
        } else if (sub === 'sixes') {
            return <>
                <th style={{ textAlign: 'right' }}>Sixes</th>
                <th style={{ textAlign: 'right' }}>Runs</th>
            </>
        } else if (sub === 'fours') {
            return <>
                <th style={{ textAlign: 'right' }}>Fours</th>
                <th style={{ textAlign: 'right' }}>Runs</th>
            </>
        } else if (['thirties', 'twenties', 'tens'].includes(sub)) {
            return <>
                <th style={{ textAlign: 'right' }}>Count</th>
                <th style={{ textAlign: 'right' }}>Runs</th>
            </>
        } else if (sub === 'wickets') {
            return <>
                <th style={{ textAlign: 'right' }}>Wkts</th>
                <th style={{ textAlign: 'right' }}>Econ</th>
                <th style={{ textAlign: 'right' }}>Avg</th>
            </>
        } else if (sub === 'economy') {
            return <>
                <th style={{ textAlign: 'right' }}>Econ</th>
                <th style={{ textAlign: 'right' }}>Wkts</th>
            </>
        } else if (sub === 'catches') return <th style={{ textAlign: 'right' }}>Catches</th>
        else if (sub === 'runouts') return <th style={{ textAlign: 'right' }}>Run Outs</th>
        else if (sub === 'stumpings') return <th style={{ textAlign: 'right' }}>Stumpings</th>
    }

    const renderRowCells = (leader) => {
        const sub = category.sub
        if (sub === 'runs') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.total_runs}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.matches}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.strike_rate || '-'}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.fours}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.sixes}</td>
            </>
        } else if (sub === 'sixes') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.total_sixes}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.total_runs}</td>
            </>
        } else if (sub === 'fours') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.total_fours}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.total_runs}</td>
            </>
        } else if (sub === 'thirties') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.thirties}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.total_runs}</td>
            </>
        } else if (sub === 'twenties') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.twenties}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.total_runs}</td>
            </>
        } else if (sub === 'tens') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.tens}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.total_runs}</td>
            </>
        } else if (sub === 'wickets') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.total_wickets}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.economy || '-'}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.average || '-'}</td>
            </>
        } else if (sub === 'economy') {
            return <>
                <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.economy}</td>
                <td style={{ textAlign: 'right', color: 'var(--text-muted)' }}>{leader.total_wickets}</td>
            </>
        } else if (sub === 'catches') return <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.total_catches}</td>
        else if (sub === 'runouts') return <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.total_runouts}</td>
        else if (sub === 'stumpings') return <td className="stat-highlight" style={{ textAlign: 'right' }}>{leader.total_stumpings}</td>
    }

    return (
        <div className="app-shell" style={{ paddingBottom: 80 }}>
            <header className="app-header">
                <div className="header-content">
                    <div className="page-title">🏆 Leaderboards</div>
                </div>
            </header>

            <div className="container">
                {/* Main Category Selector */}
                <div className="category-selector">
                    <button onClick={() => handleMainCatChange('batting')} className={`cat-btn ${category.main === 'batting' ? 'active' : ''}`}>Batting</button>
                    <button onClick={() => handleMainCatChange('bowling')} className={`cat-btn ${category.main === 'bowling' ? 'active' : ''}`}>Bowling</button>
                    <button onClick={() => handleMainCatChange('fielding')} className={`cat-btn ${category.main === 'fielding' ? 'active' : ''}`}>Fielding</button>
                </div>

                {/* Sub Category Pills */}
                <div className="pills-scroll">
                    {subCategories[category.main].map(item => (
                        <button
                            key={item.key}
                            className={`stat-pill ${category.sub === item.key ? 'active' : ''}`}
                            onClick={() => setCategory({ ...category, sub: item.key })}
                        >
                            {item.label}
                        </button>
                    ))}
                </div>

                <div className="table-responsive">
                    <table className="premium-table-primary">
                        <thead>
                            <tr>
                                <th style={{ width: 30 }}>#</th>
                                <th>Player</th>
                                {renderTableHeaders()}
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan="8" style={{ textAlign: 'center', padding: 20 }}>Loading...</td></tr>
                            ) : leaders.length === 0 ? (
                                <tr>
                                    <td colSpan="8" style={{ textAlign: 'center', padding: 40, color: 'var(--text-muted)' }}>
                                        <div style={{ fontSize: 40, marginBottom: 10 }}>📊</div>
                                        No data available yet.<br />Play matches to see stats!
                                    </td>
                                </tr>
                            ) : (
                                leaders.map((leader, index) => (
                                    <tr key={index}>
                                        <td className={`rank rank-${index + 1}`}>{index + 1}</td>
                                        <td>
                                            <div className="player-info">
                                                {leader.photo_url ? (
                                                    <img src={leader.photo_url} className="player-avatar" />
                                                ) : (
                                                    <div className="player-avatar">
                                                        {leader.name ? leader.name.substring(0, 1).toUpperCase() : '?'}
                                                    </div>
                                                )}
                                                <span className="player-name">{leader.name}</span>
                                            </div>
                                        </td>
                                        {renderRowCells(leader)}
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

            </div>
            <BottomNav />
        </div>
    )
}

export default Stats
