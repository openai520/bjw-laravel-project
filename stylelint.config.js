// stylelint 配置
export default {
  extends: [
    'stylelint-config-standard',
    'stylelint-config-recommended',
  ],
  ignoreFiles: ['public/', 'vendor/', 'node_modules/'],
  rules: {
    'color-no-invalid-hex': true,
    'declaration-block-no-duplicate-properties': true,
    'block-no-empty': true,
    'no-descending-specificity': null,
    'selector-class-pattern': null,
    'at-rule-no-unknown': [true, {
      ignoreAtRules: ['tailwind', 'apply', 'variants', 'responsive', 'screen'],
    }],
  },
}; 