import './bootstrap';

import Alpine from 'alpinejs';  // ADD THIS

// Register your invoiceForm on window BEFORE Alpine starts!
window.invoiceForm = function() {
    return {
        invoice_id: '',
        items: [
            {desc: '', qty: 1, price: 0, vat: 21}
        ],
        addLine() {
            this.items.push({desc: '', qty: 1, price: 0, vat: 21});
        },
        lineTotal(i) {
            return (parseFloat(i.qty) || 0) * (parseFloat(i.price) || 0) * (1 + (parseFloat(i.vat) || 0) / 100);
        },
        vatGroups() {
            const groups = {};
            this.items.forEach(i => {
                const key = i.vat;
                const gross = this.lineTotal(i);
                if (!groups[key]) groups[key] = {sub: 0, vat: 0};
                groups[key].sub += gross;
            });
            return groups;
        },
        grandTotal() {
            return this.items.reduce((sum, i) => sum + this.lineTotal(i), 0);
        }
    }
}

window.Alpine = Alpine;
Alpine.start();
