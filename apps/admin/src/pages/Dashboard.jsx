import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import MatchesList from '../components/MatchesList'
import TeamsList from '../components/TeamsList'
import PlayersList from '../components/PlayersList'
import SeriesList from '../components/SeriesList'
import UsersList from '../components/UsersList'

function Dashboard() {
    const [activeTab, setActiveTab] = useState('dashboard')
    const navigate = useNavigate()

    return (
        <div className="admin-container">
            <header className="admin-header">
                <h1>CricApp Admin</h1>
                <div className="user-info">Logged in as Admin</div>
            </header>

            <div className="admin-layout">
                <aside className="sidebar">
                    <button
                        className={activeTab === 'dashboard' ? 'active' : ''}
                        onClick={() => setActiveTab('dashboard')}
                    >
                        Dashboard
                    </button>
                    <button
                        className={activeTab === 'matches' ? 'active' : ''}
                        onClick={() => setActiveTab('matches')}
                    >
                        Matches
                    </button>
                    <button
                        className={activeTab === 'teams' ? 'active' : ''}
                        onClick={() => setActiveTab('teams')}
                    >
                        Teams
                    </button>
                    <button
                        className={activeTab === 'players' ? 'active' : ''}
                        onClick={() => setActiveTab('players')}
                    >
                        Players
                    </button>
                    <button
                        className={activeTab === 'series' ? 'active' : ''}
                        onClick={() => setActiveTab('series')}
                    >
                        Series
                    </button>
                    <button
                        className={activeTab === 'users' ? 'active' : ''}
                        onClick={() => setActiveTab('users')}
                    >
                        Users
                    </button>
                </aside>

                <main className="content">
                    {activeTab === 'dashboard' && (
                        <div className="dashboard-view">
                            <h2>Dashboard</h2>
                            <div className="stats-cards">
                                <div className="card">
                                    <h3>Matches</h3>
                                    <button className="btn-primary" onClick={() => setActiveTab('matches')}>Manage Matches</button>
                                </div>
                                <div className="card">
                                    <h3>Teams</h3>
                                    <button className="btn-primary" onClick={() => setActiveTab('teams')}>Manage Teams</button>
                                </div>
                                <div className="card">
                                    <h3>Series</h3>
                                    <button className="btn-primary" onClick={() => setActiveTab('series')}>Manage Series</button>
                                </div>
                            </div>
                        </div>
                    )}

                    {activeTab === 'matches' && <MatchesList />}
                    {activeTab === 'teams' && <TeamsList />}
                    {activeTab === 'players' && <PlayersList />}
                    {activeTab === 'series' && <SeriesList />}
                    {activeTab === 'users' && <UsersList />}

                </main>
            </div>
        </div>
    )
}

export default Dashboard
