import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import TelegramIntegration from './pages/TelegramIntegration'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/shops/:shopId/growth/telegram" element={<TelegramIntegration />} />
        <Route path="*" element={<Navigate to="/shops/1/growth/telegram" replace />} />
      </Routes>
    </BrowserRouter>
  )
}

export default App
