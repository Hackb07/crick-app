<?php
/**
 * Public Portal - Home Page (React Version)
 * Modern React frontend with upgraded design
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$matchModel = new MatchModel();
$liveMatches = $matchModel->getLiveMatches();
$recentMatches = $matchModel->getRecentMatches(5);
$scheduledMatches = $matchModel->getScheduledMatches();

$matchesData = [
    'live' => $liveMatches,
    'recent' => $recentMatches,
    'scheduled' => $scheduledMatches
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cricket Scoring - Live Scores & Match Updates</title>
    
    <!-- React 18 CDN (Shared Hosting Compatible) -->
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        #root {
            min-height: 100vh;
            padding-bottom: 80px;
        }
    </style>
</head>
<body>
    <div id="root"></div>

    <!-- React App Script -->
    <script src="/cricapp/assets/js/react-app.js"></script>
    
    <script type="text/babel">
        const { useState, useEffect, useCallback } = React;
        
        // Match Card Component
        const MatchCard = ({ match }) => {
            const badgeText = {
                'live': 'LIVE',
                'completed': 'Completed',
                'scheduled': 'Scheduled',
                'draft': 'Draft'
            }[match.state] || match.state;
            
            const link = match.state === 'live' 
                ? `/cricapp/public/live-match.php?id=${match.match_id}`
                : `/cricapp/public/match-view.php?id=${match.match_id}`;
            
            const buttonText = match.state === 'live' ? 'View Live' : 'View Details';
            
            const formatDate = (date) => {
                return new Date(date).toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
            };
            
            return (
                <div className={`react-match-card ${match.state === 'live' ? 'live' : ''}`}>
                    <div className="react-match-header">
                        <span className={`status-badge ${match.state}`}>{badgeText}</span>
                        <span className="series-name">{match.series_name || 'Match'}</span>
                    </div>
                    <div className="react-match-teams">
                        <div className="react-match-team">{match.team1_name}</div>
                        <div className="react-match-vs">vs</div>
                        <div className="react-match-team">{match.team2_name}</div>
                    </div>
                    <div className="react-match-info">
                        {match.match_date && (
                            <div className="match-date">{formatDate(match.match_date)}</div>
                        )}
                        <a href={link} className="react-button">{buttonText}</a>
                    </div>
                </div>
            );
        };
        
        // Loading Spinner Component
        const LoadingSpinner = ({ message }) => (
            <div className="react-loading">
                <div className="spinner"></div>
                {message && <p>{message}</p>}
            </div>
        );
        
        // Empty State Component
        const EmptyState = ({ icon, title, message }) => (
            <div className="react-empty">
                <div className="react-empty-icon">{icon || '📭'}</div>
                <div className="react-empty-title">{title}</div>
                {message && <div className="react-empty-message">{message}</div>}
            </div>
        );
        
        // Main App Component
        const App = () => {
            const [matches, setMatches] = useState(<?= json_encode($matchesData) ?>);
            const [loading, setLoading] = useState({
                live: false,
                recent: false,
                scheduled: false
            });
            
            const refreshLiveMatches = useCallback(async () => {
                setLoading(prev => ({ ...prev, live: true }));
                try {
                    const data = await ReactApp.apiClient.getMatches({ state: 'live' });
                    if (data.success && data.data) {
                        setMatches(prev => ({ ...prev, live: data.data }));
                    }
                } catch (error) {
                    console.error('Failed to refresh live matches:', error);
                } finally {
                    setLoading(prev => ({ ...prev, live: false }));
                }
            }, []);
            
            useEffect(() => {
                // Auto-refresh live matches every 10 seconds
                if (matches.live && matches.live.length > 0) {
                    const interval = setInterval(refreshLiveMatches, 10000);
                    return () => clearInterval(interval);
                }
            }, [matches.live, refreshLiveMatches]);
            
            return (
                <>
                    {/* Header */}
                    <header className="react-header">
                        <div className="react-header-content">
                            <div className="react-header-title">
                                🏏 Cricket Scoring
                            </div>
                            <nav style={{ display: 'flex', gap: '0.5rem' }}>
                                <a href="/cricapp/public/" className="react-button secondary" style={{ color: 'white', background: 'rgba(255,255,255,0.2)' }}>Home</a>
                                <a href="/cricapp/public/matches.php" className="react-button secondary" style={{ color: 'white', background: 'rgba(255,255,255,0.2)' }}>Matches</a>
                                <a href="/cricapp/public/leaderboard.php" className="react-button secondary" style={{ color: 'white', background: 'rgba(255,255,255,0.2)' }}>Leaderboard</a>
                            </nav>
                        </div>
                    </header>

                    {/* Main Content */}
                    <main className="react-container">
                        {/* Live Matches Section */}
                        <section className="react-section">
                            <h2>🔥 Live Matches</h2>
                            {loading.live ? (
                                <LoadingSpinner message="Loading live matches..." />
                            ) : matches.live.length === 0 ? (
                                <EmptyState icon="📺" title="No Live Matches" message="Check back soon for live cricket action!" />
                            ) : (
                                <div className="react-grid">
                                    {matches.live.map(match => (
                                        <MatchCard key={match.match_id} match={match} />
                                    ))}
                                </div>
                            )}
                        </section>

                        {/* Recent Matches Section */}
                        <section className="react-section">
                            <h2>📅 Recent Matches</h2>
                            {loading.recent ? (
                                <LoadingSpinner />
                            ) : matches.recent.length === 0 ? (
                                <EmptyState icon="📭" title="No Recent Matches" />
                            ) : (
                                <>
                                    <div className="react-grid">
                                        {matches.recent.map(match => (
                                            <MatchCard key={match.match_id} match={match} />
                                        ))}
                                    </div>
                                    <div style={{ textAlign: 'center', marginTop: '1.5rem' }}>
                                        <a href="/cricapp/public/recent-matches.php" className="react-button secondary">View All Recent Matches</a>
                                    </div>
                                </>
                            )}
                        </section>

                        {/* Scheduled Matches Section */}
                        <section className="react-section">
                            <h2>📆 Scheduled Matches</h2>
                            {loading.scheduled ? (
                                <LoadingSpinner />
                            ) : matches.scheduled.length === 0 ? (
                                <EmptyState icon="📅" title="No Scheduled Matches" />
                            ) : (
                                <>
                                    <div className="react-grid">
                                        {matches.scheduled.map(match => (
                                            <MatchCard key={match.match_id} match={match} />
                                        ))}
                                    </div>
                                    <div style={{ textAlign: 'center', marginTop: '1.5rem' }}>
                                        <a href="/cricapp/public/scheduled-matches.php" className="react-button secondary">View All Scheduled Matches</a>
                                    </div>
                                </>
                            )}
                        </section>
                    </main>

                    {/* Bottom Navigation */}
                    <nav className="react-bottom-nav">
                        <a href="/cricapp/public/" className="react-nav-item active">
                            <span className="react-nav-icon">🏠</span>
                            <span className="react-nav-label">Home</span>
                        </a>
                        <a href="/cricapp/public/matches.php" className="react-nav-item">
                            <span className="react-nav-icon">📅</span>
                            <span className="react-nav-label">Matches</span>
                        </a>
                        <a href="/cricapp/public/live.php" className="react-nav-item">
                            <span className="react-nav-icon">⚡</span>
                            <span className="react-nav-label">Live</span>
                        </a>
                        <a href="/cricapp/public/leaderboard.php" className="react-nav-item">
                            <span className="react-nav-icon">🏆</span>
                            <span className="react-nav-label">Leaderboard</span>
                        </a>
                        <a href="/cricapp/public/profile.php" className="react-nav-item">
                            <span className="react-nav-icon">👤</span>
                            <span className="react-nav-label">Profile</span>
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

