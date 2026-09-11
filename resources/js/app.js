// Custom Alpine Currency/Money Directive & Helpers for ArtaLedger
// Formats numbers with dot (.) thousand separators while syncing clean raw numbers to Livewire

window.formatMoneyId = function (value) {
    if (value === null || value === undefined || value === '') return '';
    let numStr = value.toString().replace(/[^0-9,-]/g, '');
    let parts = numStr.split(',');
    let intPart = parts[0];
    let isNegative = intPart.startsWith('-');
    if (isNegative) intPart = intPart.substring(1);

    // Add dot as thousands separator
    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (isNegative) intPart = '-' + intPart;

    return parts.length > 1 ? intPart + ',' + parts[1] : intPart;
};

window.parseMoneyRaw = function (value) {
    if (value === null || value === undefined || value === '') return '';
    let clean = value.toString().replace(/\./g, '').replace(',', '.');
    let num = parseFloat(clean);
    return isNaN(num) ? '' : num;
};

// Global Terbilang / Scale Label helper (Ribuan, Jutaan, Miliar, Triliun)
window.getTerbilangScale = function (value) {
    let num = typeof value === 'number' ? value : window.parseMoneyRaw(value);
    if (!num || isNaN(num) || num === 0) return '';
    let abs = Math.abs(num);
    if (abs >= 1_000_000_000_000) {
        return (num / 1_000_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Triliun';
    } else if (abs >= 1_000_000_000) {
        return (num / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Miliar';
    } else if (abs >= 1_000_000) {
        return (num / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Juta';
    } else if (abs >= 1_000) {
        return (num / 1_000).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' Ribu';
    }
    return '';
};
