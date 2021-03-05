"use strict";

module.exports = {
  // Adding a description to all types
  types: [
    {
      value: "build",
      name: "build: Project build or external dependencies changes"
    },
    { value: "docs", name: "docs: Documentation updates" },
    { value: "feat", name: "feat: New features adding" },
    { value: "fix", name: "fix: Errors fixing" },
    {
      value: "perf",
      name: "perf: Performance improvements"
    },
    {
      value: "refactor",
      name:
        "refactor: Code edits without fixing bugs or adding new features"
    },
    { value: "revert", name: "revert: Roll back to the previous commits" },
    {
      value: "style",
      name:
        "style: Code style fixes"
    },
    { value: "test", name: "test: Tests adding" }
  ],

  // Scope. It describes the code part that was affected by the changes
  scopes: [
    { name: "commitizen" },
    { name: "git" },
    { name: "docker" },
    { name: "cms" },
    { name: "rest-api" },
    { name: "shipping-method" },
  ],

  // Возможность задать спец ОБЛАСТЬ для определенного типа коммита (пример для 'fix')
  /*
  scopeOverrides: {
    fix: [
      {name: 'merge'},
      {name: 'style'},
      {name: 'e2eTest'},
      {name: 'unitTest'}
    ]
  },
  */

  // Поменяем дефолтные вопросы
  messages: {
    type: "What changes are you making?",
    scope: "Select the SCOPE that you have modified (optional):",
    // Спросим если allowCustomScopes в true
    customScope: "Custom SCOPE:",
    subject: "Write a SHORT description in the IMPERATIVE mood:\n",
    body:
      'Write a DETAILED description (optional). Use " | " for a new line:\n',
    breaking: "List of BREAKING CHANGES (optional):\n",
    footer:
      "Metadata (tickets, links, and so on). For example: MARKT-700, MARKT-800:\n",
    confirmCommit: "Are you satisfied with the resulting commit?"
  },

  // Разрешим собственную ОБЛАСТЬ
  allowCustomScopes: true,

  // Запрет на Breaking Changes
  allowBreakingChanges: false,

  // Префикс для нижнего колонтитула
  footerPrefix: "META DATA:",

  // limit subject length
  subjectLimit: 72
};
