 // Theme toggle
    document.getElementById('themeToggle')?.addEventListener('click', () => {
      document.body.classList.toggle('theme-dark');
    });

    // Role for client-side checks
    const ROLE = "<?php echo htmlspecialchars($role); ?>";

    // Action handlers (View/Edit/Delete)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-action]');
      if (!btn) return;

      const id = btn.dataset.id;
      const action = btn.dataset.action;

      // Members can only view
      if (ROLE !== 'admin' && action !== 'view') {
        alert('You do not have permission for this action.');
        return;
      }

      if (action === 'view') {
        // Replace with your real details route
        window.location.href = `Details.php?id=${encodeURIComponent(id)}`;
      }

      if (action === 'edit') {
        // Replace with your real edit route
        window.location.href = `edit.php?id=${encodeURIComponent(id)}`;
      }

      if (action === 'delete') {
        if (!confirm('Delete this record? This cannot be undone.')) return;
        try {
          const res = await fetch('../api/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
          });
          const json = await res.json();
          if (json.success) location.reload();
          else alert(json.error || 'Delete failed');
        } catch {
          alert('Network error. Try again.');
        }
      }
    });

    // Status changes
    document.querySelectorAll('.status-dropdown .menu button').forEach(item => {
      item.addEventListener('click', async () => {
        if (ROLE !== 'admin') { alert('Permission denied'); return; }

        const row = item.closest('tr');
        const idBtn = row.querySelector('[data-id]');
        const id = idBtn?.dataset.id;
        const status = item.dataset.status;

        try {
          const res = await fetch('../api/status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, status })
          });
          const json = await res.json();
          if (json.success) location.reload();
          else alert(json.error || 'Status update failed');
        } catch {
          alert('Network error. Try again.');
        }
      });
    });

    // Optional refresh
    document.getElementById('refreshBtn')?.addEventListener('click', () => location.reload());