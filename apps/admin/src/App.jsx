import { useState, useEffect } from 'react'
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import Dashboard from './pages/Dashboard'
import Login from './pages/Login'
import Scorer from './scorer/Scorer'
import './App.css'

function App() {
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Check for existing token
    const token = localStorage.getItem('token')
    const savedUser = localStorage.getItem('user')

    if (token && savedUser) {
      setUser(JSON.parse(savedUser))
      // Verify token validity logic could go here
    }
    setLoading(false)
  }, [])

  const handleLogin = (user, token) => {
    setUser(user)
  }

  const ProtectedRoute = ({ children }) => {
    if (loading) return <div>Loading...</div>
    if (!user) return <Navigate to="/login" />
    return children
  }

  return (
    <BrowserRouter basename={import.meta.env.DEV ? '/' : '/cricapp/apps/admin/dist'}>
      <Routes>
        <Route path="/login" element={<Login onLogin={handleLogin} />} />

        <Route path="/" element={
          <ProtectedRoute>
            <Dashboard />
          </ProtectedRoute>
        } />

        <Route path="/scorer/:matchId" element={
          <ProtectedRoute>
            <Scorer />
          </ProtectedRoute>
        } />
      </Routes>
    </BrowserRouter>
  )
}

export default App
