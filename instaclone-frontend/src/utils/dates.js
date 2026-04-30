const dayMonthYear = new Intl.DateTimeFormat('pt-BR', {
  day: '2-digit',
  month: 'short',
  year: 'numeric',
})

const dayMonth = new Intl.DateTimeFormat('pt-BR', {
  day: '2-digit',
  month: 'short',
})

const fullDateTime = new Intl.DateTimeFormat('pt-BR', {
  day: '2-digit',
  month: 'long',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
})

const shortDateTime = new Intl.DateTimeFormat('pt-BR', {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
})

const MINUTE = 60 * 1000
const HOUR = 60 * MINUTE
const DAY = 24 * HOUR
const WEEK = 7 * DAY

export function formatDayMonthYear(value) {
  return value ? dayMonthYear.format(new Date(value)) : ''
}

export function formatFullDateTime(value) {
  return value ? fullDateTime.format(new Date(value)) : ''
}

export function formatShortDateTime(value) {
  return value ? shortDateTime.format(new Date(value)) : ''
}

export function formatRelative(value) {
  if (!value) {
    return ''
  }

  const diffMs = Math.max(0, Date.now() - new Date(value).getTime())

  if (diffMs < HOUR) {
    return `${Math.max(1, Math.floor(diffMs / MINUTE))} min`
  }
  if (diffMs < DAY) {
    return `${Math.floor(diffMs / HOUR)} h`
  }
  if (diffMs < WEEK) {
    return `${Math.floor(diffMs / DAY)} d`
  }
  return dayMonth.format(new Date(value))
}
