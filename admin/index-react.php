<?php
/**
 * Admin Dashboard - React Version
 * Modern admin panel with React
 */

session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['token'])) {
    header('Location: /cricapp/admin/login.php');
    exit;
}

require_once __DIR__ . '/../includes/bootstrap.php';

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLiveMatches();
$recentMatches = $matchModel->getRecentMatches(10);

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cricket Scoring</title>
    
    <!-- React 18 CDN -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    
    <!-- Babel Standalone for JSX -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- Modern CSS -->
    <link rel="stylesheet" href="/cricapp/assets/css/main.css">
    <link rel="stylesheet" href="/cricapp/assets/css/vue-modern.css">
    <link rel="stylesheet" href="/cricapp/assets/css/react-modern.css">
    
    <style>
        body {
            background: var(--gray-50);
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        #root {
            min-height: 100vh;
            padding-bottom: 100px;
        }
        
        .admin-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div id="root"></div>

    <!-- React App Script -->
    <script src="/cricapp/assets/js/react-app.js"></script>
    
    <script type="text/babel">
        const { useState, useEffect } = React;
        
        // Set token for API client
        ReactApp.apiClient.setToken('<?= $_SESSION['token'] ?>');
        
        // Stat Card Component
        const StatCard = ({ label, value, buttonText, buttonLink, buttonClass = 'primary' }) => (
            <div className="stat-card">
                <div className="stat-label">{label}</div>
                <div className="stat-value">{value}</div>
                {buttonLink && (
                    <a href={buttonLink} className={`react-button ${buttonClass}`} style={{ width: '100%' }}>
                        {buttonText}
                    </a>
                )}
            </div>
        );
        
        // Loading Spinner
        const LoadingSpinner = () => (
            <div className="react-loading">
                <div className="spinner"></div>
            </div>
        );
        
        // Empty State
        const EmptyState = ({ icon, title }) => (
            <div className="react-empty">
                <div className="react-empty-icon">{icon || '📭'}</div>
                <div className="react-empty-title">{title}</div>
            </div>
        );
        
        // Match Table Row
        const MatchRow = ({ match }) => {
            const formatDate = (date) => {
                return new Date(date).toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
            };
            
            return (
                <tr>
                    <td>{formatDate(match.match_date)}</td>
                    <td>{match.team1_name} vs {match.team2_name}</td>
                    <td>{match.series_name || '-'}</td>
                    <td>
                        <span className={`status-badge ${match.state}`}>{match.state}</span>
                    </td>
                    <td>
                        <a href={`/cricapp/admin/matches/view.php?id=${match.match_id}`} 
                           className="react-button" 
                           style={{ padding: '0.375rem 0.75rem', fontSize: '0.75rem' }}>
                            View
                        </a>
                    </td>
                </tr>
            );
        };
        
        // Main App Component
        const App = () => {
            const [matches, setMatches] = useState({
                live: <?= json_encode($liveMatches) ?>,
                recent: <?= json_encode($recentMatches) ?>
            });
            const [loading, setLoading] = useState(false);
            const user = <?= json_encode($user) ?>;
            
            useEffect(() => {
                // Auto-refresh every 30 seconds
                const interval = setInterval(async () => {
                    setLoading(true);
                    try {
                        const liveData = await ReactApp.apiClient.getMatches({ state: 'live' });
                        if (liveData.success && liveData.data) {
                            setMatches(prev => ({ ...prev, live: liveData.data }));
                        }
                    } catch (error) {
                        console.error('Failed to refresh:', error);
                    } finally {
                        setLoading(false);
                    }
                }, 30000);
                
                return () => clearInterval(interval);
            }, []);
            
            return (
                <>
                    <div className="react-container">
                        {/* Admin Header */}
                        <div className="react-header" style={{ borderRadius: 'var(--radius-xl)', marginBottom: '2rem' }}>
                            <div className="react-header-content">
                                <div className="react-header-title">Admin Dashboard</div>
                                <div style={{ fontSize: '0.875rem', opacity: 0.9 }}>
                                    Welcome, {user.username}
                                </div>
                            </div>
                        </div>

                        {/* Stats Grid */}
                        <div className="admin-stats-grid">
                            <StatCard
                                label="Live Matches"
                                value={matches.live.length}
                                buttonText="View Live"
                                buttonLink="/cricapp/admin/matches/?state=live"
                            />
                            <StatCard
                                label="Recent Matches"
                                value={matches.recent.length}
                                buttonText="View All"
                                buttonLink="/cricapp/admin/matches/?state=completed"
                                buttonClass="secondary"
                            />
                            <StatCard
                                label="Quick Actions"
                                value=""
                                buttonText=""
                                buttonLink=""
                            >
                                <div style={{ display: 'flex', gap: '0.75rem', flexWrap: 'wrap' }}>
                                    <a href="/cricapp/admin/matches/create.php" className="react-button success">Create Match</a>
                                    <a href="/cricapp/admin/players/create.php" className="react-button secondary">Add Player</a>
                                </div>
                            </StatCard>
                        </div>

                        {/* Recent Matches Table */}
                        <div className="react-section">
                            <h2 style={{ marginTop: 0, marginBottom: '1rem', color: 'var(--gray-900)' }}>Recent Matches</h2>
                            {loading ? (
                                <LoadingSpinner />
                            ) : matches.recent.length === 0 ? (
                                <EmptyState icon="📭" title="No Recent Matches" />
                            ) : (
                                <div style={{ overflowX: 'auto' }}>
                                    <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                        <thead>
                                            <tr>
                                                <th style={{ textAlign: 'left', padding: '0.75rem', fontWeight: 600, color: 'var(--gray-700)', borderBottom: '2px solid var(--gray-200)' }}>Date</th>
                                                <th style={{ textAlign: 'left', padding: '0.75rem', fontWeight: 600, color: 'var(--gray-700)', borderBottom: '2px solid var(--gray-200)' }}>Teams</th>
                                                <th style={{ textAlign: 'left', padding: '0.75rem', fontWeight: 600, color: 'var(--gray-700)', borderBottom: '2px solid var(--gray-200)' }}>Series</th>
                                                <th style={{ textAlign: 'left', padding: '0.75rem', fontWeight: 600, color: 'var(--gray-700)', borderBottom: '2px solid var(--gray-200)' }}>Status</th>
                                                <th style={{ textAlign: 'left', padding: '0.75rem', fontWeight: 600, color: 'var(--gray-700)', borderBottom: '2px solid var(--gray-200)' }}>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {matches.recent.map(match => (
                                                <MatchRow key={match.match_id} match={match} />
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Admin Bottom Bar */}
                    <nav className="react-bottom-nav">
                        <a href="/cricapp/admin/" className="react-nav-item active">
                            <span className="react-nav-icon">🏠</span>
                            <span className="react-nav-label">Dashboard</span>
                        </a>
                        <a href="/cricapp/admin/matches/" className="react-nav-item">
                            <span className="react-nav-icon">🏏</span>
                            <span className="react-nav-label">Matches</span>
                        </a>
                        <a href="/cricapp/admin/players/" className="react-nav-item">
                            <span className="react-nav-icon">👥</span>
                            <span className="react-nav-label">Players</span>
                        </a>
                        <a href="/cricapp/admin/settings/" className="react-nav-item">
                            <span className="react-nav-icon">⚙️</span>
                            <span className="react-nav-label">Settings</span>
                        </a>
                    </nav>
                </>
            );
        };
        
        // Render App
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>

