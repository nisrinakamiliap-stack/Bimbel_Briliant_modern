<style>
    .auth-section { background-color: #F8FAFC; padding: 3rem 0 4.5rem; }
    .auth-grid { display: grid; grid-template-columns: 1fr; gap: 2rem; align-items: stretch; max-width: 1040px; margin: 0 auto; }
    .auth-grid.auth-grid--single { max-width: 560px; }

    .auth-brand-panel { display: none; position: relative; overflow: hidden; border-radius: 1.25rem; padding: 2.75rem; color: #ffffff; background: linear-gradient(150deg, #142B4A 0%, #0e1f36 55%, #16845B 140%); }
    .auth-brand-panel::after { content: ''; position: absolute; width: 18rem; height: 18rem; right: -6rem; bottom: -6rem; border-radius: 50%; background-color: rgba(22,132,91,.35); }
    .auth-brand-panel h2 { font-size: 1.75rem; font-weight: 800; line-height: 1.25; margin: 1.25rem 0 1rem; }
    .auth-brand-panel p { font-size: .9375rem; line-height: 1.7; color: rgba(255,255,255,.75); margin: 0 0 1.75rem; }
    .auth-brand-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: .875rem; position: relative; z-index: 1; }
    .auth-brand-list li { display: flex; align-items: flex-start; gap: .75rem; font-size: .9375rem; color: rgba(255,255,255,.9); }
    .auth-brand-list svg { flex-shrink: 0; margin-top: 2px; color: #86efac; }
    .auth-badge { display: inline-flex; align-items: center; gap: .5rem; background-color: rgba(22,132,91,.3); color: #86efac; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: .3rem .8rem; border-radius: 999px; }

    .auth-card { background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 1.25rem; padding: 2rem 1.5rem; box-shadow: 0 10px 30px rgba(20,43,74,.06); }
    .auth-card h1 { font-size: 1.625rem; font-weight: 800; color: #142B4A; margin: 0 0 .5rem; line-height: 1.25; }
    .auth-subtitle { font-size: .9375rem; color: #64748b; margin: 0 0 1.75rem; line-height: 1.6; }

    .auth-form { display: flex; flex-direction: column; gap: 1.25rem; }
    .auth-label { display: block; font-size: .875rem; font-weight: 600; color: #142B4A; margin-bottom: .375rem; }
    .auth-label .req { color: #dc2626; }
    .auth-input { width: 100%; padding: .75rem 1rem; border-radius: .5rem; border: 1px solid #cbd5e1; background-color: #ffffff; font-size: .9375rem; color: #253247; outline: none; transition: border-color .2s, box-shadow .2s; box-sizing: border-box; font-family: inherit; }
    .auth-input:focus { border-color: #16845B; box-shadow: 0 0 0 3px rgba(22,132,91,.15); }
    .auth-input.is-invalid { border-color: #dc2626; }
    .auth-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,38,38,.15); }
    .auth-hint { font-size: .75rem; color: #94a3b8; margin: .375rem 0 0; }
    .auth-error { font-size: .8125rem; color: #dc2626; margin: .375rem 0 0; }

    .auth-password-wrap { position: relative; }
    .auth-password-wrap .auth-input { padding-right: 3rem; }
    .auth-toggle-password { position: absolute; top: 50%; right: .375rem; transform: translateY(-50%); display: inline-flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border: none; background: none; color: #64748b; cursor: pointer; border-radius: .375rem; }
    .auth-toggle-password:hover { color: #142B4A; background-color: #f1f5f9; }
    .auth-toggle-password:focus-visible { outline: 2px solid #16845B; outline-offset: 1px; }

    .auth-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .75rem; }
    .auth-checkbox { display: inline-flex; align-items: center; gap: .5rem; font-size: .875rem; color: #475569; cursor: pointer; }
    .auth-checkbox input { width: 1rem; height: 1rem; accent-color: #16845B; cursor: pointer; }

    .auth-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; padding: .875rem 1.5rem; font-size: 1rem; font-weight: 700; color: #ffffff; background-color: #16845B; border: none; border-radius: .5rem; cursor: pointer; text-decoration: none; transition: background-color .2s, box-shadow .2s; font-family: inherit; }
    .auth-btn:hover { background-color: #126c4a; box-shadow: 0 6px 16px rgba(22,132,91,.25); }
    .auth-btn:focus-visible { outline: 3px solid rgba(22,132,91,.4); outline-offset: 2px; }
    .auth-btn--navy { background-color: #142B4A; }
    .auth-btn--navy:hover { background-color: #0e1f36; box-shadow: 0 6px 16px rgba(20,43,74,.25); }
    .auth-btn--auto { width: auto; }

    .auth-link { color: #16845B; font-weight: 600; text-decoration: none; }
    .auth-link:hover { text-decoration: underline; }
    .auth-link:focus-visible { outline: 2px solid #16845B; outline-offset: 2px; border-radius: 2px; }
    .auth-footer-text { font-size: .9375rem; color: #64748b; text-align: center; margin: 1.5rem 0 0; }

    .auth-alert { display: flex; align-items: flex-start; gap: .75rem; border-radius: .75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem; font-size: .9375rem; line-height: 1.6; }
    .auth-alert--success { background-color: #f0fdf4; border: 1px solid #86efac; color: #166534; }
    .auth-alert--error { background-color: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .auth-alert svg { flex-shrink: 0; margin-top: 3px; }

    .auth-two-col { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }

    @media (min-width: 640px) {
        .auth-card { padding: 2.5rem; }
        .auth-two-col { grid-template-columns: 1fr 1fr; }
    }

    @media (min-width: 1024px) {
        .auth-grid { grid-template-columns: 1fr 1fr; }
        .auth-grid.auth-grid--single { grid-template-columns: 1fr; }
        .auth-brand-panel { display: block; }
    }
</style>
