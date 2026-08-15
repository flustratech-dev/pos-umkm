/**
 * Password Strength Meter Helper for Register Form
 * Compliant with PRD-Frontend-POS-Kasir-UMKM-Event.md section 2.1 & 3
 */

export function evaluatePasswordStrength(password = '') {
    if (!password) {
        return {
            score: 0,
            label: 'Belum diisi',
            color: 'bg-gray-200 text-gray-400',
            barColor: 'bg-gray-200',
            width: '0%',
            checks: {
                length: false,
                uppercase: false,
                lowercase: false,
                number: false,
                special: false,
            }
        };
    }

    const checks = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password),
    };

    let passedCount = 0;
    if (checks.length) passedCount++;
    if (checks.uppercase && checks.lowercase) passedCount++;
    if (checks.number) passedCount++;
    if (checks.special) passedCount++;

    // Classify into 4 levels: Sangat Lemah / Lemah (1), Sedang (2), Kuat (3), Sangat Kuat (4)
    if (passedCount <= 1) {
        return {
            score: 1,
            label: 'Lemah',
            color: 'text-rose-600',
            barColor: 'bg-rose-500',
            width: '25%',
            checks
        };
    } else if (passedCount === 2) {
        return {
            score: 2,
            label: 'Sedang',
            color: 'text-amber-600',
            barColor: 'bg-amber-500',
            width: '50%',
            checks
        };
    } else if (passedCount === 3) {
        return {
            score: 3,
            label: 'Kuat',
            color: 'text-emerald-600',
            barColor: 'bg-emerald-500',
            width: '75%',
            checks
        };
    } else {
        return {
            score: 4,
            label: 'Sangat Kuat',
            color: 'text-teal-600 font-bold',
            barColor: 'bg-teal-600',
            width: '100%',
            checks
        };
    }
}
