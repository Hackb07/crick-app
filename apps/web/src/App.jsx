import { BrowserRouter, Routes, Route } from 'react-router-dom'
import Home from './pages/Home'
import MatchDetail from './pages/MatchDetail'
import Matches from './pages/Matches'
import Series from './pages/Series'
import Stats from './pages/Stats'
import './App.css'

function App() {
  return (
    <BrowserRouter basename={import.meta.env.DEV ? '/' : '/cricapp/apps/web/dist'}>
      <div className="app-container">
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/match/:id" element={<MatchDetail />} />
          <Route path="/matches" element={<Matches />} />
          <Route path="/series" element={<Series />} />
          <Route path="/stats" element={<Stats />} />
        </Routes>
      </div>
    </BrowserRouter>
  )
}

export default App
