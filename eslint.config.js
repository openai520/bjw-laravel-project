// ESLint 9.x 配置
export default {
  ignores: ['node_modules/', 'public/', 'vendor/', 'storage/', '*.min.js'],
  files: ['**/*.{js,jsx,ts,tsx}'],
  languageOptions: {
    ecmaVersion: 2022,
    sourceType: 'module',
  },
  rules: {
    'no-unused-vars': 'warn',
    'no-duplicate-imports': 'warn',
    'no-console': 'warn',
    'no-debugger': 'warn',
    'no-redeclare': 'warn',
    'no-unreachable': 'warn',
    'no-empty': 'warn',
    'no-extra-semi': 'warn',
  },
}; 