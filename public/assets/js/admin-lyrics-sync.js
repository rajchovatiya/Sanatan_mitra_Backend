// Tap-to-sync for mantra lyrics: play the audio and press "Mark line" (or Space)
// as each line begins; "[mm:ss.xx] " is written in front of that line in the
// chosen language's lyrics box.
(function () {
    const box = document.querySelector('.lyrics-sync');
    if (!box) return;

    const audio = box.querySelector('[data-sync-audio]');
    const langSelect = box.querySelector('[data-sync-lang]');
    const startBtn = box.querySelector('[data-sync-start]');
    const markBtn = box.querySelector('[data-sync-mark]');
    const undoBtn = box.querySelector('[data-sync-undo]');
    const clearBtn = box.querySelector('[data-sync-clear]');
    const status = box.querySelector('[data-sync-status]');
    const TAGS = /^[ \t]*(?:\[\d{1,3}:\d{2}(?:[.:]\d{1,3})?\][ \t]*)+/;

    let lines = [];
    let order = [];   // indexes of the lines that have words
    let next = 0;     // position in `order` of the line to mark next
    let running = false;

    const textarea = () => document.querySelector(`textarea[name="lyrics[${langSelect.value}]"]`);

    const stamp = (seconds) => {
        const m = Math.floor(seconds / 60);
        const s = seconds - m * 60;
        return `[${String(m).padStart(2, '0')}:${s.toFixed(2).padStart(5, '0')}] `;
    };

    const write = () => { textarea().value = lines.join('\n'); };

    const show = () => {
        if (!running) return;
        if (next >= order.length) {
            status.textContent = 'All lines are marked. Press Save to keep the times.';
            return;
        }
        status.textContent = `Line ${next + 1} of ${order.length} next: "${lines[order[next]].replace(TAGS, '').trim()}"`;
    };

    startBtn.addEventListener('click', () => {
        const area = textarea();
        if (!area || !area.value.trim()) {
            status.textContent = 'Write the lyrics for this language first.';
            return;
        }
        lines = area.value.split('\n').map((l) => l.replace(TAGS, ''));
        order = lines.map((l, i) => (l.trim() ? i : -1)).filter((i) => i >= 0);
        next = 0;
        running = true;
        write();
        markBtn.disabled = false;
        undoBtn.disabled = false;
        audio.currentTime = 0;
        audio.play();
        show();
        markBtn.focus();
    });

    const mark = () => {
        if (!running || next >= order.length) return;
        const i = order[next];
        lines[i] = stamp(audio.currentTime) + lines[i].replace(TAGS, '');
        next++;
        write();
        show();
        if (next >= order.length) audio.pause();
    };
    markBtn.addEventListener('click', mark);

    undoBtn.addEventListener('click', () => {
        if (!running || next === 0) return;
        next--;
        const i = order[next];
        lines[i] = lines[i].replace(TAGS, '');
        write();
        // Go back a little so the line can be marked again.
        audio.currentTime = Math.max(0, audio.currentTime - 3);
        show();
    });

    clearBtn.addEventListener('click', () => {
        const area = textarea();
        if (!area) return;
        area.value = area.value.split('\n').map((l) => l.replace(TAGS, '')).join('\n');
        running = false;
        markBtn.disabled = true;
        undoBtn.disabled = true;
        status.textContent = 'Times removed for this language. Press Save to keep the change.';
    });

    // Space marks the line while syncing (but not while typing in a box).
    document.addEventListener('keydown', (e) => {
        if (!running || e.code !== 'Space') return;
        const tag = (e.target.tagName || '').toLowerCase();
        if (tag === 'textarea' || tag === 'input' || tag === 'select') return;
        e.preventDefault();
        mark();
    });
})();
