import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import './Login.css'

function Login({ onLogin }) {
    const [username, setUsername] = useState('')
    const [password, setPassword] = useState('')
    const [error, setError] = useState(null)
    const [loading, setLoading] = useState(false)
    const navigate = useNavigate()

    const handleSubmit = (e) => {
        e.preventDefault()
        setLoading(true)
        setError(null)

        fetch('/api/v1/auth.php?path=/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        })
            .then(res => res.json())
            .then(data => {
                setLoading(false)
                if (data.success) {
                    // Store token
                    localStorage.setItem('token', data.token)
                    localStorage.setItem('user', JSON.stringify(data.user))

                    // Notify App component
                    if (onLogin) onLogin(data.user, data.token)

                    navigate('/')
                } else {
                    setError(data.error || 'Login failed')
                }
            })
            .catch(err => {
                setLoading(false)
                setError('Network error: ' + err.message)
            })
    }

    return (
        <div className="login-container">
            <div className="login-card">
                <h2>CricApp Admin</h2>
                <p className="subtitle">Sign in to manage cricket matches</p>

                {error && <div className="error-message">{error}</div>}

                <form onSubmit={handleSubmit}>
                    <div className="form-group">
                        <label>Username</label>
                        <input
                            type="text"
                            value={username}
                            onChange={e => setUsername(e.target.value)}
                            placeholder="admin"
                            required
                        />
                    </div>

                    <div className="form-group">
                        <label>Password</label>
                        <input
                            type="password"
                            value={password}
                            onChange={e => setPassword(e.target.value)}
                            placeholder="••••••••"
                            required
                        />
                    </div>

                    <button type="submit" className="btn-primary btn-block" disabled={loading}>
                        {loading ? 'Signing in...' : 'Sign In'}
                    </button>
                </form>
            </div>
        </div>
    )
}

export default Login
