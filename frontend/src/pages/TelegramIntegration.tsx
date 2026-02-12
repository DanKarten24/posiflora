import { useState, useEffect, useCallback } from 'react'
import { useParams } from 'react-router-dom'

interface Status {
  enabled: boolean
  chatId: string
  lastSentAt: string | null
  sentCount: number
  failedCount: number
}

function TelegramIntegration() {
  const { shopId } = useParams<{ shopId: string }>()
  const [botToken, setBotToken] = useState('')
  const [chatId, setChatId] = useState('')
  const [enabled, setEnabled] = useState(false)
  const [status, setStatus] = useState<Status | null>(null)
  const [saving, setSaving] = useState(false)
  const [message, setMessage] = useState('')

  const loadStatus = useCallback(async () => {
    try {
      const res = await fetch(`/shops/${shopId}/telegram/status`)
      if (res.ok) {
        setStatus(await res.json())
      }
    } catch {
      // интеграция не настроена
    }
  }, [shopId])

  useEffect(() => {
    loadStatus()
  }, [loadStatus])

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault()
    setMessage('')

    if (!/^\d+:[A-Za-z0-9_-]+$/.test(botToken)) {
      setMessage('Неверный формат Bot Token (ожидается: 123456:ABC-DEF...)')
      return
    }

    if (!/^-?\d+$/.test(chatId)) {
      setMessage('Chat ID должен быть числом')
      return
    }

    setSaving(true)

    try {
      const res = await fetch(`/shops/${shopId}/telegram/connect`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ botToken, chatId, enabled }),
      })

      if (res.ok) {
        setMessage('Настройки сохранены')
        await loadStatus()
      } else {
        const data = await res.json()
        setMessage(data.error || 'Ошибка сохранения')
      }
    } catch {
      setMessage('Ошибка соединения с сервером')
    } finally {
      setSaving(false)
    }
  }

  return (
    <div style={{ maxWidth: 600, margin: '40px auto', fontFamily: 'sans-serif' }}>
      <h1>Telegram-интеграция</h1>

      <form onSubmit={handleSave} style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
        <label>
          Bot Token
          <input
            type="text"
            value={botToken}
            onChange={e => setBotToken(e.target.value)}
            placeholder="123456:ABC-DEF..."
            style={{ display: 'block', width: '100%', padding: 8, marginTop: 4 }}
          />
        </label>

        <label>
          Chat ID
          <input
            type="text"
            value={chatId}
            onChange={e => setChatId(e.target.value)}
            placeholder="1234567890"
            style={{ display: 'block', width: '100%', padding: 8, marginTop: 4 }}
          />
        </label>

        <label style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <input
            type="checkbox"
            checked={enabled}
            onChange={e => setEnabled(e.target.checked)}
          />
          Включить уведомления
        </label>

        <button type="submit" disabled={saving} style={{ padding: '10px 20px', cursor: 'pointer' }}>
          {saving ? 'Сохранение...' : 'Сохранить'}
        </button>

        {message && <p style={{ color: message === 'Настройки сохранены' ? 'green' : 'red' }}>{message}</p>}
      </form>

      {status && (
        <div style={{ marginTop: 32, padding: 16, background: '#f5f5f5', borderRadius: 8 }}>
          <h2>Статус интеграции</h2>
          <p>Статус: <strong>{status.enabled ? 'Активна' : 'Выключена'}</strong></p>
          <p>Chat ID: <code>{status.chatId}</code></p>
          <p>Последняя отправка: {status.lastSentAt ? new Date(status.lastSentAt + 'Z').toLocaleString('ru-RU') : '—'}</p>
          <p>За 7 дней: отправлено {status.sentCount}, ошибок {status.failedCount}</p>
        </div>
      )}

      <details style={{ marginTop: 32 }}>
        <summary style={{ cursor: 'pointer' }}>Где взять Bot Token?</summary>
        <ol style={{ lineHeight: 1.8 }}>
          <li>Откройте <strong>@BotFather</strong> в Telegram</li>
          <li>Отправьте /newbot и следуйте инструкциям</li>
          <li>Скопируйте Bot Token</li>
          <li>Перейдите в бот и нажмите /start</li>
        </ol>
      </details>

      <details style={{ marginTop: 16 }}>
        <summary style={{ cursor: 'pointer' }}>Как узнать Chat ID?</summary>
        <ol style={{ lineHeight: 1.8 }}>
          <li>Откройте <strong>@Getmyid_Work_Bot</strong> в Telegram и нажмите /start</li>
          <li>Скопируйте полученный Chat ID</li>
        </ol>
      </details>
    </div>
  )
}

export default TelegramIntegration
