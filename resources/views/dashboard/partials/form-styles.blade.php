<style>
    .type-btn {
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
        min-height: 92px; padding: 12px 8px; border-radius: 12px;
        border: 1px solid #1a2b3c;
        background: rgba(4,10,16,0.75);
        color: var(--text-secondary);
        cursor: pointer; transition: all 0.25s;
        font-family: var(--font-display);
        font-size: 0.72rem; letter-spacing: .03em;
    }
    .type-btn:hover {
        border-color: var(--accent);
        color: #fff;
        transform: translateY(-2px);
    }
    .type-btn-active {
        border-color: var(--accent-bright) !important;
        background: rgba(45,159,212,0.16) !important;
        color: #fff !important;
        box-shadow: 0 0 24px rgba(79,195,247,.3), inset 0 0 0 1px rgba(79,195,247,.4);
    }
    .type-btn svg { transition: color 0.2s, filter 0.2s; }
    .type-btn-active svg { color: var(--accent-ultra); stroke: var(--accent-ultra); filter: drop-shadow(0 0 8px rgba(79,195,247,1)); }
    .s-gen-btn {
        display: inline-flex; align-items: center; gap: 6px; min-height: 30px; padding: 0 12px;
        font-size: 0.7rem; color: var(--accent-ultra); background: rgba(79,195,247,0.1);
        border: 1px solid rgba(79,195,247,0.35); border-radius: 999px; cursor: pointer;
        font-family: var(--font-display); transition: all .2s;
    }
    .s-gen-btn:hover { background: rgba(79,195,247,0.2); color: #fff; box-shadow: 0 0 16px rgba(79,195,247,.4); }
    @media (max-width: 560px) {
        form [style*="grid-template-columns: repeat(3, 1fr)"] { grid-template-columns: repeat(2, 1fr) !important; }
        form [style*="grid-template-columns: 1fr 1fr"], form [style*="grid-template-columns: 2fr 1fr"] { grid-template-columns: 1fr !important; }
    }
</style>
