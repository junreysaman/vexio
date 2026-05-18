<!-- SERVER / LANGUAGE SELECTOR -->
      <div class="server-bar">
        <div class="container">
          <div class="server-inner">
            <span class="server-label">Server</span>
            <div class="server-tabs">
              <button class="server-tab active" onclick="selectServer(this,'VX-1')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                VX-1 <span style="color:var(--green);font-size:9px;">●</span>
              </button>
              <button class="server-tab" onclick="selectServer(this,'VX-2')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                VX-2
              </button>
              <button class="server-tab" onclick="selectServer(this,'HD-Fast')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                HD-Fast
              </button>
              <button class="server-tab" onclick="selectServer(this,'Backup')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12H2m20 0-4 4m4-4-4-4M2 12l4 4M2 12l4-4"></path></svg>
                Backup
              </button>
            </div>
            <div class="server-divider"></div>
            <div class="lang-tabs">
              <button class="lang-tab active sub" onclick="selectLang(this,'sub')">SUB</button>
              <button class="lang-tab dub" onclick="selectLang(this,'dub')">DUB</button>
            </div>
          </div>
        </div>
      </div>
