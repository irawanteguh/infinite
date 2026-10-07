function esc(v) {
    return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function italicizeForeignText(text) {

    if (!text) return "";

    // Escape HTML terlebih dahulu
    text = esc(text);

    // Daftar kata / istilah asing yang umum digunakan dalam dokumen ICD/iDRG
    const foreignWords = [
        'acute',
        'chronic',
        'congenital',
        'acquired',
        'anterior',
        'posterior',
        'superior',
        'inferior',
        'multiple',
        'single',
        'bilateral',
        'unilateral',
        'primary',
        'secondary',
        'unspecified',
        'other',
        'specified',
        'without',
        'with',
        'disease',
        'disorder',
        'syndrome',
        'failure',
        'infection',
        'infarction',
        'fracture',
        'hemorrhage',
        'neoplasm',
        'malignant',
        'benign',
        'carcinoma',
        'metastasis',
        'diabetes',
        'mellitus',
        'hypertension',
        'pneumonia',
        'sepsis',
        'stroke',
        'cancer',
        'tumor',
        'injury',
        'trauma',
        'complication',
        'procedure',
        'diagnosis',
        'treatment',
        'clinical',
        'medical',
        'surgical',
        'history',
        'status',
        'thrombolytic',
        'agent',
        'injection',
        'infusion',
        'ischemic',
        'myopathy',
        'epilepsy',
        'periodic',
        'paralysis',
        'adhesiolysis',
        'spinal',
        'cord',
        'nerve',
        'root',
        'steroid',
        'analgesic',
        'analgetic',
        'adrenal',
        'pituitary',
        'pineal',
        'gland',
        'thymus',
        'diagnostic',
        'mechanical',
        'instrument',
        'canal',
        'omit',
        'code',
        'and',
        'glands',
        'procedures',
        'on'
    ];

    const pattern = new RegExp(
        '\\b(' + foreignWords.join('|') + ')\\b',
        'gi'
    );

    return text.replace(
        pattern,'<i style="font-weight:500;color:#3f4254;">$1</i>'
    );
}