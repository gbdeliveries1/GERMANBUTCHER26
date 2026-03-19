<?php
// Handle Page Fetching
$action = $_GET['action'] ?? 'list';
$page_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action == 'edit' || $action == 'add') {
    $page = [
        'id' => 0, 'title' => '', 'slug' => '', 'status' => 1, 
        'meta_title' => '', 'meta_description' => '', 
        'show_in_header' => 0, 'show_in_footer' => 0, 
        'content_blocks' => '[]'
    ];
    if ($page_id > 0) {
        $res = $conn->query("SELECT * FROM custom_pages WHERE id = $page_id");
        if ($res && $res->num_rows > 0) $page = $res->fetch_assoc();
    }
    $blocks = json_decode($page['content_blocks'], true) ?: [];
?>
    <!-- PAGE EDITOR VIEW -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        .pb-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .pb-layout { display: flex; gap: 20px; align-items: flex-start; }
        .pb-main { flex: 1; min-width: 0; }
        .pb-sidebar { width: 320px; flex-shrink: 0; display: flex; flex-direction: column; gap: 20px; position: sticky; top: 20px; }
        
        .pb-card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .pb-card h3 { margin-top: 0; font-size: 15px; color: #ff5000; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 13px; color: #444; }
        .form-group input[type="text"], .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-family: inherit; }
        
        .toggle-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; font-size: 14px; font-weight: 600; color: #444; }
        .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #ff5000; }
        input:checked + .slider:before { transform: translateX(20px); }

        .block-toolbar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #eee; }
        .btn-add-block { background: #fff; border: 1px solid #ddd; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 12px; transition: 0.2s; }
        .btn-add-block:hover { background: #ff5000; color: #fff; border-color: #ff5000; }
        
        .builder-area { min-height: 400px; padding: 15px; background: #f0f2f5; border-radius: 8px; border: 2px dashed #ccc; }
        .pb-block { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 15px; position: relative; }
        .pb-block-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; font-weight: bold; color: #333; }
        .drag-handle { cursor: grab; margin-right: 10px; color: #aaa; font-size: 18px; }
        .btn-remove-block { color: #ff4747; background: none; border: none; cursor: pointer; font-weight: bold; font-size: 12px; }
        
        .btn-save { background: #10b981; color: #fff; border: none; padding: 14px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; width: 100%; transition: 0.3s; }
        .btn-save:hover { background: #059669; }
    </style>

    <div class="pb-header">
        <h2><?php echo $page_id ? '✏️ Edit Page: '.htmlspecialchars($page['title']) : '📄 Create New Page'; ?></h2>
        <a href="?page=admin_manager&manage=page_builder" class="btn-add-block" style="font-size:14px; padding:10px 15px;">← Back to List</a>
    </div>

    <div class="pb-layout">
        <!-- LEFT: BUILDER -->
        <div class="pb-main">
            <div class="pb-card" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label style="font-size: 16px;">Page Title (H1)</label>
                    <input type="text" id="page_title" value="<?php echo htmlspecialchars($page['title']); ?>" placeholder="e.g. About Us" style="font-size: 18px; padding: 12px;" onkeyup="updateSlug(this.value)">
                </div>
            </div>

            <div class="block-toolbar">
                <strong style="width: 100%; font-size: 13px; color: #666; margin-bottom: 5px;">Add Content Blocks:</strong>
                <button class="btn-add-block" onclick="addBlock('text')">📝 Text / HTML</button>
                <button class="btn-add-block" onclick="addBlock('image')">🖼️ Image</button>
                <button class="btn-add-block" onclick="addBlock('banner')">🏳️ Hero Banner</button>
                <button class="btn-add-block" onclick="addBlock('video')">▶️ Video Embed</button>
                <button class="btn-add-block" onclick="addBlock('product_grid')">📦 Products Grid</button>
                <button class="btn-add-block" onclick="addBlock('category_grid')">📁 Category Grid</button>
                <button class="btn-add-block" onclick="addBlock('button')">🔘 Link Button</button>
            </div>

            <div class="builder-area" id="builder-canvas">
                <!-- Blocks injected here -->
            </div>
        </div>

        <!-- RIGHT: SETTINGS SIDEBAR -->
        <div class="pb-sidebar">
            <button class="btn-save" onclick="savePage()" id="btn-save-page">💾 Publish Page</button>

            <div class="pb-card">
                <h3>⚙️ Page Settings</h3>
                <div class="form-group">
                    <label>Status</label>
                    <select id="page_status">
                        <option value="1" <?php echo $page['status']==1?'selected':'';?>>🟢 Published (Visible)</option>
                        <option value="0" <?php echo $page['status']==0?'selected':'';?>>⚪ Draft (Hidden)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>URL Slug</label>
                    <input type="text" id="page_slug" value="<?php echo htmlspecialchars($page['slug']); ?>" placeholder="about-us">
                    <small style="color: #888;">Example: yoursite.com/index.php?page_slug=<b>about-us</b></small>
                </div>
            </div>

            <div class="pb-card">
                <h3>🔗 Menu Placement</h3>
                <div class="toggle-row">
                    <span>Show in Header Menu</span>
                    <label class="switch"><input type="checkbox" id="show_header" <?php echo $page['show_in_header']?'checked':'';?>><span class="slider"></span></label>
                </div>
                <div class="toggle-row">
                    <span>Show in Footer Links</span>
                    <label class="switch"><input type="checkbox" id="show_footer" <?php echo $page['show_in_footer']?'checked':'';?>><span class="slider"></span></label>
                </div>
            </div>

            <div class="pb-card">
                <h3>🔍 SEO Settings</h3>
                <div class="form-group">
                    <label>Meta Title</label>
                    <input type="text" id="meta_title" value="<?php echo htmlspecialchars($page['meta_title']); ?>" placeholder="Custom SEO Title">
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea id="meta_desc" rows="3" placeholder="Brief summary for Google search results..."><?php echo htmlspecialchars($page['meta_description']); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <script>
    let blockData = <?php echo json_encode($blocks); ?>;
    const canvas = document.getElementById('builder-canvas');
    Sortable.create(canvas, { handle: '.drag-handle', animation: 150 });

    function updateSlug(val) {
        if(!document.getElementById('page_slug').value || <?php echo $page_id; ?> === 0) {
            document.getElementById('page_slug').value = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        }
    }

    const templates = {
        text: (d) => `<div class="form-group"><label>Text Content (HTML allowed)</label><textarea rows="5" class="b-content">${d.content||''}</textarea></div>`,
        image: (d) => `<div class="form-group"><label>Image URL</label><input type="text" class="b-url" value="${d.url||''}" placeholder="e.g. images/about.jpg"></div><div class="form-group"><label>Link (Optional)</label><input type="text" class="b-link" value="${d.link||''}"></div>`,
        banner: (d) => `<div class="form-group"><label>Title</label><input type="text" class="b-title" value="${d.title||''}"></div><div class="form-group"><label>Subtitle</label><input type="text" class="b-subtitle" value="${d.subtitle||''}"></div><div class="form-group"><label>Background Image URL</label><input type="text" class="b-bg" value="${d.bg_image||''}"></div>`,
        video: (d) => `<div class="form-group"><label>YouTube / Vimeo Embed URL</label><input type="text" class="b-url" value="${d.url||''}" placeholder="https://www.youtube.com/embed/XXXX"></div>`,
        product_grid: (d) => `<div class="form-group"><label>Section Title</label><input type="text" class="b-title" value="${d.title||'Featured Products'}"></div><div class="form-group"><label>Number of Products</label><input type="number" class="b-limit" value="${d.limit||4}"></div>`,
        category_grid: (d) => `<div class="form-group"><label>Section Title</label><input type="text" class="b-title" value="${d.title||'Shop by Category'}"></div><div class="form-group"><label>Limit</label><input type="number" class="b-limit" value="${d.limit||4}"></div>`,
        button: (d) => `<div class="form-group"><label>Button Text</label><input type="text" class="b-text" value="${d.text||'Click Here'}"></div><div class="form-group"><label>Link URL</label><input type="text" class="b-link" value="${d.link||'#'}"></div>`
    };

    function renderBlock(type, data = {}) {
        let div = document.createElement('div');
        div.className = 'pb-block';
        div.dataset.type = type;
        div.innerHTML = `
            <div class="pb-block-header">
                <div><span class="drag-handle">☰</span> ${type.replace('_', ' ').toUpperCase()}</div>
                <button class="btn-remove-block" onclick="if(confirm('Remove this block?')) this.closest('.pb-block').remove()">✖ Remove</button>
            </div>
            <div class="pb-block-body">${templates[type](data)}</div>
        `;
        canvas.appendChild(div);
    }

    function addBlock(type) { renderBlock(type); }
    blockData.forEach(b => { if(templates[b.type]) renderBlock(b.type, b); });

    function savePage() {
        let title = document.getElementById('page_title').value;
        let slug = document.getElementById('page_slug').value;
        if(!title || !slug) return alert("Title and Slug are required!");

        let btn = document.getElementById('btn-save-page');
        btn.innerText = "Saving..."; btn.disabled = true;

        let blocks = [];
        document.querySelectorAll('.pb-block').forEach(el => {
            let type = el.dataset.type;
            let block = { type: type };
            if(type === 'text') block.content = el.querySelector('.b-content').value;
            if(type === 'image') { block.url = el.querySelector('.b-url').value; block.link = el.querySelector('.b-link').value; }
            if(type === 'video') block.url = el.querySelector('.b-url').value;
            if(type === 'banner') { block.title = el.querySelector('.b-title').value; block.subtitle = el.querySelector('.b-subtitle').value; block.bg_image = el.querySelector('.b-bg').value; }
            if(type === 'product_grid' || type === 'category_grid') { block.title = el.querySelector('.b-title').value; block.limit = el.querySelector('.b-limit').value; }
            if(type === 'button') { block.text = el.querySelector('.b-text').value; block.link = el.querySelector('.b-link').value; }
            blocks.push(block);
        });

        let fd = new FormData();
        fd.append('id', <?php echo $page_id; ?>);
        fd.append('title', title);
        fd.append('slug', slug);
        fd.append('status', document.getElementById('page_status').value);
        fd.append('meta_title', document.getElementById('meta_title').value);
        fd.append('meta_desc', document.getElementById('meta_desc').value);
        fd.append('show_header', document.getElementById('show_header').checked ? 1 : 0);
        fd.append('show_footer', document.getElementById('show_footer').checked ? 1 : 0);
        fd.append('blocks', JSON.stringify(blocks));

        fetch('ajax_page_builder.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                window.location.href = '?page=admin_manager&manage=page_builder&success=Page Saved Successfully';
            } else { 
                alert(data.message); 
                btn.innerText = "💾 Publish Page"; btn.disabled = false;
            }
        });
    }
    </script>

<?php
} else {
    // PAGE LIST VIEW
    $pages = $conn->query("SELECT * FROM custom_pages ORDER BY created_at DESC");
?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2 style="margin:0;">📄 Page Management</h2>
            <p style="color:#666; margin:5px 0 0 0;">Create and manage custom pages, landing pages, and their menu visibility.</p>
        </div>
        <a href="?page=admin_manager&manage=page_builder&action=add" style="background:#ff5000; color:#fff; padding:12px 20px; border-radius:6px; text-decoration:none; font-weight:bold;">+ Create New Page</a>
    </div>

    <table class="ali-table" style="width: 100%; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <thead>
            <tr style="background: #f8f9fa; text-align: left;">
                <th style="padding: 15px;">Page Info</th>
                <th style="padding: 15px;">Menu Visibility</th>
                <th style="padding: 15px;">Status</th>
                <th style="padding: 15px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($pages && $pages->num_rows > 0): while($p = $pages->fetch_assoc()): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;">
                    <strong style="font-size:15px; display:block; margin-bottom:4px;"><?php echo htmlspecialchars($p['title']); ?></strong>
                    <a href="../index.php?page_slug=<?php echo $p['slug']; ?>" target="_blank" style="color:#3b82f6; font-size:12px;">/<?php echo $p['slug']; ?></a>
                </td>
                <td style="padding: 15px; font-size: 13px; color: #555;">
                    <?php if($p['show_in_header']) echo '<span style="background:#e0f2fe; color:#0284c7; padding:2px 6px; border-radius:4px; margin-right:5px;">Header</span>'; ?>
                    <?php if($p['show_in_footer']) echo '<span style="background:#f3e8ff; color:#7e22ce; padding:2px 6px; border-radius:4px;">Footer</span>'; ?>
                    <?php if(!$p['show_in_header'] && !$p['show_in_footer']) echo '<span style="color:#aaa;">Unlinked</span>'; ?>
                </td>
                <td style="padding: 15px;">
                    <span style="padding:4px 10px; border-radius:15px; font-size:12px; font-weight:bold; background:<?php echo $p['status']?'#d1fae5':'#f3f4f6'; ?>; color:<?php echo $p['status']?'#065f46':'#4b5563'; ?>;">
                        <?php echo $p['status'] ? 'Published' : 'Draft'; ?>
                    </span>
                </td>
                <td style="padding: 15px; text-align: right;">
                    <a href="?page=admin_manager&manage=page_builder&action=edit&id=<?php echo $p['id']; ?>" style="color:#10b981; text-decoration:none; font-weight:bold; margin-right:15px;"><i class="fas fa-edit"></i> Edit</a>
                    <a href="#" onclick="deletePage(<?php echo $p['id']; ?>)" style="color:#ff4747; text-decoration:none; font-weight:bold;"><i class="fas fa-trash"></i> Delete</a>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="4" style="padding: 30px; text-align: center; color: #888;">No pages created yet. Click "Create New Page" to start.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    function deletePage(id) {
        if(confirm("Warning: Are you sure you want to completely delete this page?")) {
            let fd = new FormData(); fd.append('delete_id', id);
            fetch('ajax_page_builder.php', { method: 'POST', body: fd })
            .then(res => res.json()).then(data => { window.location.reload(); });
        }
    }
    </script>
<?php } ?>