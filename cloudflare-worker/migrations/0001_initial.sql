-- WP AI Gateway Database Schema
-- D1 SQLite database for activity logging and analytics

-- Activity log for all WordPress API requests
CREATE TABLE IF NOT EXISTS activity_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  site_id TEXT NOT NULL,
  endpoint TEXT NOT NULL,
  method TEXT NOT NULL,
  status INTEGER NOT NULL,
  cached INTEGER DEFAULT 0,
  duration_ms INTEGER,
  request_body TEXT,
  response_size INTEGER,
  error_message TEXT,
  created_at INTEGER DEFAULT (unixepoch())
);

-- Indexes for common queries
CREATE INDEX IF NOT EXISTS idx_activity_site_time ON activity_log(site_id, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_activity_endpoint ON activity_log(endpoint);
CREATE INDEX IF NOT EXISTS idx_activity_cached ON activity_log(cached, created_at DESC);

-- Cache invalidation tracking
CREATE TABLE IF NOT EXISTS cache_invalidations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  site_id TEXT NOT NULL,
  pattern TEXT NOT NULL,
  reason TEXT,
  invalidated_count INTEGER DEFAULT 0,
  created_at INTEGER DEFAULT (unixepoch())
);

-- Site health monitoring
CREATE TABLE IF NOT EXISTS site_health (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  site_id TEXT NOT NULL,
  status TEXT NOT NULL, -- 'healthy', 'degraded', 'down'
  response_time_ms INTEGER,
  error_message TEXT,
  checked_at INTEGER DEFAULT (unixepoch())
);

CREATE INDEX IF NOT EXISTS idx_health_site_time ON site_health(site_id, checked_at DESC);

-- Aggregated daily stats (for fast dashboard queries)
CREATE TABLE IF NOT EXISTS daily_stats (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  site_id TEXT NOT NULL,
  date TEXT NOT NULL, -- YYYY-MM-DD
  total_requests INTEGER DEFAULT 0,
  cache_hits INTEGER DEFAULT 0,
  cache_misses INTEGER DEFAULT 0,
  avg_duration_ms REAL DEFAULT 0,
  error_count INTEGER DEFAULT 0,
  UNIQUE(site_id, date)
);

CREATE INDEX IF NOT EXISTS idx_daily_stats_date ON daily_stats(date DESC);

-- Views for common analytics queries

-- Cache hit rate by site
CREATE VIEW IF NOT EXISTS v_cache_hit_rate AS
SELECT
  site_id,
  COUNT(*) as total_requests,
  SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) as cache_hits,
  ROUND(100.0 * SUM(CASE WHEN cached = 1 THEN 1 ELSE 0 END) / COUNT(*), 2) as hit_rate_percent,
  AVG(duration_ms) as avg_duration_ms
FROM activity_log
WHERE created_at > unixepoch() - 86400
GROUP BY site_id;

-- Slowest endpoints
CREATE VIEW IF NOT EXISTS v_slow_endpoints AS
SELECT
  site_id,
  endpoint,
  method,
  COUNT(*) as request_count,
  AVG(duration_ms) as avg_duration_ms,
  MAX(duration_ms) as max_duration_ms
FROM activity_log
WHERE created_at > unixepoch() - 86400 AND cached = 0
GROUP BY site_id, endpoint, method
ORDER BY avg_duration_ms DESC
LIMIT 50;

-- Error summary
CREATE VIEW IF NOT EXISTS v_error_summary AS
SELECT
  site_id,
  endpoint,
  status,
  COUNT(*) as error_count,
  MAX(error_message) as last_error
FROM activity_log
WHERE status >= 400 AND created_at > unixepoch() - 86400
GROUP BY site_id, endpoint, status
ORDER BY error_count DESC;
