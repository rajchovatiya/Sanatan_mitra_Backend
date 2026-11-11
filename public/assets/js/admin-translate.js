// Adds a "Translate" button to every admin form that has English/Hindi/Gujarati fields.
// It sends the English text to the server and fills the Hindi and Gujarati boxes,
// which the admin can still edit before saving. Plain JavaScript, no build step.
(function () {
    const url = document.querySelector('meta[name="translate-url"]')?.content;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!url) return;

    const box = (group, lang) => group.querySelector(`[name$="[${lang}]"]`);

    function addButton(form) {
        const groups = [...form.querySelectorAll('fieldset.langs[data-field]')]
            .filter((g) => g.dataset.translate !== 'off');
        if (!groups.length) return;

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn secondary translate-btn';
        button.textContent = '🌐 Translate English → हिंदी + ગુજરાતી';
        form.insertBefore(button, groups[0]);

        button.addEventListener('click', async () => {
            const texts = {};
            for (const g of groups) {
                const en = box(g, 'en')?.value.trim();
                if (en) texts[g.dataset.field] = en;
            }
            if (!Object.keys(texts).length) {
                alert('Write the English text first.');
                return;
            }

            const hasText = groups.some((g) => ['hi', 'gu'].some((l) => box(g, l)?.value.trim()));
            const replace = !hasText || confirm(
                'Some Hindi or Gujarati boxes already have text.\n\nOK = replace them\nCancel = fill only the empty boxes'
            );

            const label = button.textContent;
            button.disabled = true;
            button.textContent = 'Translating…';
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ texts }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Translation failed. Please try again.');

                for (const [lang, values] of Object.entries(data.translations)) {
                    for (const [field, text] of Object.entries(values)) {
                        const target = form.querySelector(`fieldset[data-field="${field}"] [name$="[${lang}]"]`);
                        if (target && (replace || !target.value.trim())) target.value = text;
                    }
                }
                button.textContent = '✓ Done – please check the Hindi and Gujarati before saving';
                setTimeout(() => { button.textContent = label; }, 4000);
            } catch (e) {
                alert(e.message);
                button.textContent = label;
            } finally {
                button.disabled = false;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('form.card').forEach(addButton));
})();
