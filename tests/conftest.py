"""Shared pytest fixtures. DB access is mocked so tests run without MySQL."""

import sys
from pathlib import Path

import pytest

from unittest.mock import MagicMock

ROOT = Path(__file__).resolve().parent.parent
if str(ROOT) not in sys.path:
    sys.path.insert(0, str(ROOT))

from app import create_app


class FakeDB:
    """Configurable stand-in for the PyMySQL-backed Database wrapper."""

    def __init__(self):
        self._one = None
        self._one_router = None  # callable(sql, params) -> row or None
        self._all = []
        self._all_router = None  # callable(sql, params) -> list or None
        self._val = None
        self._insert = 1
        self.queries = []

    def fetch_one(self, sql, params=None):
        self.queries.append(("fetch_one", sql, params))
        if self._one_router is not None:
            return self._one_router(sql, params)
        return self._one

    def fetch_all(self, sql, params=None):
        self.queries.append(("fetch_all", sql, params))
        if self._all_router is not None:
            return self._all_router(sql, params)
        return self._all

    def fetch_val(self, sql, params=None):
        self.queries.append(("fetch_val", sql, params))
        return self._val

    def execute(self, sql, params=None):
        self.queries.append(("execute", sql, params))
        return 1

    def insert(self, sql, params=None):
        self.queries.append(("insert", sql, params))
        return self._insert


@pytest.fixture
def app():
    _app = create_app()
    _app.config.update(TESTING=True, SECRET_KEY="test-secret")
    return _app


@pytest.fixture
def client(app):
    return app.test_client()


@pytest.fixture
def fakedb(monkeypatch):
    fakedb = FakeDB()
    import app.helpers as helpers_mod
    monkeypatch.setattr(helpers_mod, "db", lambda: fakedb)
    monkeypatch.setattr(helpers_mod, "table_has_column", lambda table, column: True)

    for module_name in [
        "app.routes.public",
        "app.routes.auth",
        "app.routes.student",
        "app.routes.payments",
        "app.routes.trainer",
        "app.routes.admin_core",
        "app.routes.admin_content",
    ]:
        mod = __import__(module_name, fromlist=["db"])
        monkeypatch.setattr(mod, "db", lambda: fakedb)
    return fakedb
