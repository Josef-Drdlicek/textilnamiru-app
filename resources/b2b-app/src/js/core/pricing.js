export function varByCode(variables, code) {
  const item = variables.find((v) => v.code === code);
  return item ? parseFloat(item.value) : 0;
}

export function roundPrice(value) {
  return Math.round(value * 100) / 100;
}

export function withVat(netPrice, variables) {
  const vat = varByCode(variables, 'VAT');
  return roundPrice(netPrice * (1 + vat / 100));
}

export function formatMoney(value) {
  return new Intl.NumberFormat('cs-CZ', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value);
}
