document.addEventListener('DOMContentLoaded', function () {
    const visitDate = document.getElementById('visit_date');
    const visitTime = document.getElementById('visit_time');
    const totalPeople = document.getElementById('total_people');
    const ticketPriceInput = document.getElementById('ticket_price');
    const confirmButton = document.getElementById('confirmButton');
    const quotaInfo = document.getElementById('quota_info');
    const peopleWarning = document.getElementById('people_warning');

    const totalPriceText = document.getElementById('total_price_text');
    const summaryDate = document.getElementById('summary_date');
    const summaryTime = document.getElementById('summary_time');
    const summaryPeople = document.getElementById('summary_people');
    const summaryTotal = document.getElementById('summary_total');

    const ticketPrice = parseInt(ticketPriceInput.value, 10) || 0;
    let selectedRemainingQuota = 0;

    const today = new Date().toISOString().split('T')[0];
    visitDate.setAttribute('min', today);

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    }

    function calculateTotal() {
        const people = parseInt(totalPeople.value, 10) || 0;
        const total = people * ticketPrice;

        totalPriceText.textContent = formatRupiah(total);
        summaryPeople.textContent = people + ' orang';
        summaryTotal.textContent = formatRupiah(total);

        validateForm();
    }

    function updateSummary() {
        summaryDate.textContent = visitDate.value || 'Belum dipilih';
        summaryTime.textContent = visitTime.value || 'Belum dipilih';
    }

    function validateForm() {
        const people = parseInt(totalPeople.value, 10) || 0;
        const hasDate = visitDate.value !== '';
        const hasTime = visitTime.value !== '';
        const validPeople = people > 0;
        const quotaEnough = selectedRemainingQuota > 0 && people <= selectedRemainingQuota;

        if (hasTime && people > selectedRemainingQuota) {
            peopleWarning.classList.remove('d-none');
        } else {
            peopleWarning.classList.add('d-none');
        }

        confirmButton.disabled = !(hasDate && hasTime && validPeople && quotaEnough);
    }

    visitDate.addEventListener('change', function () {
        const date = this.value;

        visitTime.innerHTML = '<option value="">Memuat data kuota...</option>';
        visitTime.disabled = true;
        selectedRemainingQuota = 0;
        quotaInfo.textContent = 'Sedang mengecek kuota...';

        fetch('../ajax/check_quota.php?date=' + encodeURIComponent(date))
    .then(function (response) {
        return response.text();
    })
    .then(function (text) {
        let result;

        try {
            result = JSON.parse(text);
        } catch (error) {
            throw new Error(text);
        }

        visitTime.innerHTML = '<option value="">Pilih jam kunjungan</option>';

        if (!result.success) {
            quotaInfo.textContent = result.message || 'Gagal mengambil data kuota.';
            validateForm();
            return;
        }

        result.data.forEach(function (slot) {
            const option = document.createElement('option');

            option.value = slot.time;
            option.dataset.remaining = slot.remaining_quota;

            if (slot.is_full) {
                option.textContent = slot.time + ' - Penuh';
                option.disabled = true;
            } else {
                option.textContent = slot.time + ' - Sisa ' + slot.remaining_quota + ' orang';
            }

            visitTime.appendChild(option);
        });

        visitTime.disabled = false;
        quotaInfo.textContent = 'Pilih jam tersedia. Satu reservasi berlaku maksimal 1 jam.';

        updateSummary();
        validateForm();
    })
    .catch(function (error) {
        visitTime.innerHTML = '<option value="">Gagal memuat kuota</option>';
        quotaInfo.textContent = 'Terjadi kesalahan saat mengecek kuota. Detail: ' + error.message;
        validateForm();
    });
    });

    visitTime.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        selectedRemainingQuota = parseInt(selectedOption.dataset.remaining, 10) || 0;

        if (this.value !== '') {
            quotaInfo.textContent = 'Sisa kuota untuk jam ini: ' + selectedRemainingQuota + ' orang.';
        } else {
            quotaInfo.textContent = 'Pilih jam tersedia.';
        }

        updateSummary();
        validateForm();
    });

    totalPeople.addEventListener('input', function () {
        calculateTotal();
        validateForm();
    });

    calculateTotal();
    updateSummary();
    validateForm();
});
