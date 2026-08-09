"""Database layer: per-request PyMySQL connection with PDO-like helpers."""

from flask import current_app, g

import pymysql
import pymysql.cursors


class Database:
    """Thin wrapper around PyMySQL exposing fetch/fetch_one/execute."""

    def __init__(self, connection):
        self._conn = connection

    def _cursor(self):
        return self._conn.cursor(pymysql.cursors.DictCursor)

    def query(self, sql):
        """Run a raw query and return all rows."""
        with self._cursor() as cur:
            cur.execute(sql)
            return cur.fetchall()

    def fetch_all(self, sql, params=None):
        with self._cursor() as cur:
            cur.execute(sql, params or ())
            return cur.fetchall()

    def fetch_one(self, sql, params=None):
        with self._cursor() as cur:
            cur.execute(sql, params or ())
            return cur.fetchone()

    def fetch_val(self, sql, params=None):
        """Fetch a single scalar value (e.g. COUNT(*) or SUM())."""
        row = self.fetch_one(sql, params)
        if not row:
            return None
        values = list(row.values())
        return values[0] if values else None

    def execute(self, sql, params=None):
        with self._cursor() as cur:
            cur.execute(sql, params or ())
            self._conn.commit()
            return cur.rowcount

    def insert(self, sql, params=None):
        """Execute INSERT and return lastrowid."""
        with self._cursor() as cur:
            cur.execute(sql, params or ())
            self._conn.commit()
            return cur.lastrowid

    def column_cache(self):
        return getattr(self, "_column_cache", None)


def get_db():
    if "db" not in g:
        from config import Config

        conn = pymysql.connect(
            host=Config.DB_HOST,
            user=Config.DB_USER,
            password=Config.DB_PASS,
            database=Config.DB_NAME,
            port=Config.DB_PORT,
            charset="utf8mb4",
            cursorclass=pymysql.cursors.DictCursor,
            autocommit=False,
        )
        g.db = Database(conn)
    return g.db


def init_app(app):
    @app.teardown_appcontext
    def close_db(exc):
        db = g.pop("db", None)
        if db is not None:
            try:
                db._conn.close()
            except Exception:
                pass


db = get_db
