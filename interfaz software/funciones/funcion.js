// Paginación para la tabla de productos
// Función para crear un nuevo producto
function createNewProduct(productData) {
    const tbody = document.querySelector('.products-table tbody');
    const tr = document.createElement('tr');
	tr.className = 'product-row';
	if (productData.id) tr.setAttribute('data-id', productData.id);
    
    tr.innerHTML = `
        <td><input type="checkbox" aria-label="Seleccionar producto"></td>
        <td class="product-name">
			<img src="${productData.image || productData.imagen || '../imagenes/thumb-product-1.jpg'}" alt="" class="product-thumb">
            <span class="product-name__text" data-field="product-name">${productData.name}</span>
        </td>
        <td class="product-category" data-field="categoria">${productData.categoria}</td>
        <td class="product-sku" data-field="codigo">${productData.codigo}</td>
        <td class="product-variant" data-field="variante">${productData.variante}</td>
		<td class="product-price" data-field="precio">$${Number(productData.precio).toFixed(2)}</td>
        <td class="product-status" data-field="existencia">
            <span class="badge badge--${productData.existencia.toLowerCase() === 'active' ? 'active' : 'out'}">${productData.existencia}</span>
        </td>
        <td class="product-stock" data-field="stock">${productData.stock}</td>
        <td class="actions-menu">
            <div class="action-buttons">
                <button type="button" class="btn-action btn-edit">Editar</button>
                <button type="button" class="btn-action btn-delete">Eliminar</button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(tr, tbody.firstChild);
}

// Función para mostrar el formulario de agregar producto
function showAddProductForm() {
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'modal';
    
    modalOverlay.innerHTML = `
        <div class="modal-content">
            <h2>Agregar Nuevo Producto</h2>
            <form id="addProductForm">
                <div class="form-group">
                    <label for="product-name">Nombre del Producto:</label>
                    <input type="text" id="product-name" name="product-name" required>
                </div>
				<div class="form-group">
					<label for="descripcion">Descripción</label>
					<textarea id="descripcion" name="descripcion" rows="2"></textarea>
				</div>
                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <input type="text" id="categoria" name="categoria" required>
                </div>
                <div class="form-group">
                    <label for="codigo">Código:</label>
                    <input type="text" id="codigo" name="codigo" required>
                </div>
                <div class="form-group">
                    <label for="variante">Variante:</label>
                    <input type="text" id="variante" name="variante" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio:</label>
                    <input type="number" id="precio" name="precio" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="existencia">Existencia:</label>
                    <select id="existencia" name="existencia" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
				<div class="form-group">
					<label for="imagen">Imagen URL (opcional)</label>
					<input type="text" id="imagen" name="imagen" placeholder="../imagenes/thumb-product-1.jpg">
				</div>
                <div class="form-buttons">
                    <button type="submit" class="btn-save">Guardar</button>
                    <button type="button" class="btn-cancel">Cancelar</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(modalOverlay);

    const form = modalOverlay.querySelector('form');
    
    // Función para cerrar el modal
    const closeModal = () => {
        modalOverlay.remove();
    };

    // Manejar el envío del formulario
	form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
		const productData = {
            name: formData.get('product-name'),
            categoria: formData.get('categoria'),
            codigo: formData.get('codigo'),
            variante: formData.get('variante'),
            precio: formData.get('precio'),
            existencia: formData.get('existencia'),
            stock: formData.get('stock')
        };

		const saveBtn = form.querySelector('.btn-save');
		const originalBtnText = saveBtn ? saveBtn.textContent : 'Guardar';
		if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Guardando...'; }

		try {
			const payload = new URLSearchParams();
			payload.append('nombre', formData.get('product-name'));
			payload.append('descripcion', formData.get('descripcion') || '');
			payload.append('cantidad', formData.get('stock'));
			payload.append('precio', formData.get('precio'));
			payload.append('categoria', formData.get('categoria'));
			payload.append('codigo', formData.get('codigo'));
			payload.append('variante', formData.get('variante'));
			payload.append('existencia', formData.get('existencia'));
			payload.append('stock', formData.get('stock'));
			payload.append('imagen', formData.get('imagen') || '');

			console.info('Enviando petición a agregar.php con payload:', payload.toString());
			const res = await fetch('../inventario/agregar.php?format=json', {
				method: 'POST',
				headers: {'Content-Type': 'application/x-www-form-urlencoded'},
				body: payload.toString()
			});
			const text = await res.text();
			console.info('Respuesta raw de agregar.php:', text);
			let json;
			try { json = JSON.parse(text); } catch (e) { json = { success: false, error: 'JSON parse error: ' + e.message }; }
			if (!res.ok || !json.success) throw new Error(json.error || 'Error al guardar');
			if (typeof json.affected !== 'undefined' && Number(json.affected) <= 0) {
				throw new Error('No se insertó ninguna fila (affected=0)');
			}

			productData.id = json.id;
			createNewProduct({
				id: productData.id,
				name: productData.name,
				categoria: productData.categoria,
				codigo: productData.codigo,
				variante: productData.variante,
				precio: parseFloat(productData.precio) || 0,
				existencia: productData.existencia,
				stock: parseInt(productData.stock, 10) || 0
			});
			closeModal();

			const alert = document.createElement('div');
			alert.className = 'alert alert-success';
			alert.style.position = 'fixed';
			alert.style.top = '20px';
			alert.style.right = '20px';
			alert.style.zIndex = 9999;
			alert.textContent = 'Producto agregado correctamente';
			document.body.appendChild(alert);
			setTimeout(() => alert.remove(), 2500);
		} catch (err) {
			console.error('Error al agregar producto:', err);
			const alert = document.createElement('div');
			alert.className = 'alert alert-danger';
			alert.style.position = 'fixed';
			alert.style.top = '20px';
			alert.style.right = '20px';
			alert.style.zIndex = 9999;
			alert.textContent = 'Error al agregar producto: ' + (err.message || 'Desconocido');
			document.body.appendChild(alert);
			setTimeout(() => alert.remove(), 4000);
		} finally {
			if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = originalBtnText; }
		}
    });

    // Cerrar modal con el botón de cancelar
    modalOverlay.querySelector('.btn-cancel').addEventListener('click', closeModal);

    // Cerrar modal al hacer clic fuera del contenido
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });

    // Enfocar el primer campo
    modalOverlay.querySelector('input').focus();
}

document.addEventListener('DOMContentLoaded', function () {
	const pageSize = 6; // filas por página
	const table = document.querySelector('.products-table');
	if (!table) return;
	const tbody = table.querySelector('tbody');
	const rows = Array.from(tbody.querySelectorAll('tr'));
	const pagination = document.querySelector('.pagination');
	const pageNumbersList = pagination ? pagination.querySelector('.page-numbers') : null;
	const prevBtn = pagination ? pagination.querySelector('.page-prev') : null;
	const nextBtn = pagination ? pagination.querySelector('.page-next') : null;

	if (rows.length === 0) {
		// mostrar estado vacío
		const empty = document.createElement('div');
		empty.className = 'empty-state';
		empty.textContent = 'No hay productos';
		table.parentElement.replaceWith(empty);
		if (pagination) pagination.style.display = 'none';
		return;
	}

	const totalPages = Math.ceil(rows.length / pageSize);

	function renderPage(page) {
		// page: 1-based
		const start = (page - 1) * pageSize;
		const end = start + pageSize;
		rows.forEach((r, i) => {
			r.style.display = i >= start && i < end ? '' : 'none';
		});
		// actualizar botones
		if (pageNumbersList) {
			const btns = pageNumbersList.querySelectorAll('.page');
			btns.forEach(b => b.classList.remove('is-current'));
			const cur = pageNumbersList.querySelector('[data-page="' + page + '"]');
			if (cur) cur.classList.add('is-current');
		}
		if (prevBtn) prevBtn.disabled = page <= 1;
		if (nextBtn) nextBtn.disabled = page >= totalPages;
	}

	function buildPagination() {
		if (!pageNumbersList) return;
		// Si ya existen botones .page en el HTML (por ejemplo diseñados estáticamente),
		// no los borramos: les añadimos data-page y listeners para que funcionen.
		const existing = pageNumbersList.querySelectorAll('.page');
		if (existing.length > 0) {
			existing.forEach((btn, idx) => {
				// si ya tiene data-page, respetarlo; si no, usar el índice+1
				const pageNum = btn.getAttribute('data-page') ? Number(btn.getAttribute('data-page')) : (idx + 1);
				btn.setAttribute('data-page', pageNum);
				btn.classList.toggle('is-current', pageNum === 1);
				// evitar duplicar listeners: eliminamos y volvemos a añadir
				btn.replaceWith(btn.cloneNode(true));
			});
			// volver a seleccionar nodos clonados y añadir listeners
			const cloned = pageNumbersList.querySelectorAll('.page');
			cloned.forEach(btn => {
				const p = Number(btn.getAttribute('data-page'));
				btn.addEventListener('click', () => renderPage(p));
			});
			return;
		}

		// Si no hay botones estáticos, generarlos según totalPages
		pageNumbersList.innerHTML = '';
		for (let p = 1; p <= totalPages; p++) {
			const li = document.createElement('li');
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'page' + (p === 1 ? ' is-current' : '');
			btn.setAttribute('data-page', p);
			btn.textContent = p;
			btn.addEventListener('click', () => renderPage(p));
			li.appendChild(btn);
			pageNumbersList.appendChild(li);
		}
	}

	// eventos prev/next
	if (prevBtn) prevBtn.addEventListener('click', () => {
		const current = pageNumbersList.querySelector('.page.is-current');
		const curPage = current ? Number(current.getAttribute('data-page')) : 1;
		if (curPage > 1) renderPage(curPage - 1);
	});
	if (nextBtn) nextBtn.addEventListener('click', () => {
		const current = pageNumbersList.querySelector('.page.is-current');
		const curPage = current ? Number(current.getAttribute('data-page')) : 1;
		if (curPage < totalPages) renderPage(curPage + 1);
	});

	// inicializar
	buildPagination();
	renderPage(1);
});

// Fin de funcion.js

	// Handlers para botones visibles Editar / Eliminar
	document.addEventListener('click', function (e) {
		// Agregar nuevo producto
		if (e.target.matches('.btn-add')) {
			showAddProductForm();
			return;
		}

		// Editar
		const editBtn = e.target.closest('.btn-edit');
		if (editBtn) {
			const row = editBtn.closest('tr');
			if (!row) return;

			// Crear el overlay del modal
			const modalOverlay = document.createElement('div');
			modalOverlay.className = 'modal';
			
			// Obtener los valores actuales
			const getFieldContent = (fieldName) => {
				const element = row.querySelector(`[data-field="${fieldName}"]`);
				if (!element) return '';
				let value = element.textContent.trim();
				if (fieldName === 'precio') {
					value = value.replace('$', '').trim();
				} else if (fieldName === 'existencia') {
					value = element.querySelector('.badge')?.textContent || '';
				}
				return value;
			};

			// Crear el formulario
			modalOverlay.innerHTML = `
				<div class="modal-content">
					<h2>Editar Producto</h2>
					<form id="editProductForm">
						<div class="form-group">
							<label for="product-name">Nombre del Producto:</label>
							<input type="text" id="product-name" name="product-name" value="${getFieldContent('product-name')}" required>
						</div>
						<div class="form-group">
							<label for="categoria">Categoría:</label>
							<input type="text" id="categoria" name="categoria" value="${getFieldContent('categoria')}" required>
						</div>
						<div class="form-group">
							<label for="codigo">Código:</label>
							<input type="text" id="codigo" name="codigo" value="${getFieldContent('codigo')}" required>
						</div>
						<div class="form-group">
							<label for="variante">Variante:</label>
							<input type="text" id="variante" name="variante" value="${getFieldContent('variante')}" required>
						</div>
						<div class="form-group">
							<label for="precio">Precio:</label>
							<input type="number" id="precio" name="precio" value="${getFieldContent('precio')}" min="0" step="0.01" required>
						</div>
						<div class="form-group">
							<label for="existencia">Existencia:</label>
							<select id="existencia" name="existencia" required>
								<option value="Active" ${getFieldContent('existencia') === 'Active' ? 'selected' : ''}>Active</option>
								<option value="Inactive" ${getFieldContent('existencia') === 'Inactive' ? 'selected' : ''}>Inactive</option>
							</select>
						</div>
						<div class="form-group">
							<label for="stock">Stock:</label>
							<input type="number" id="stock" name="stock" value="${getFieldContent('stock')}" min="0" required>
						</div>
						<div class="form-buttons">
							<button type="submit" class="btn-save">Guardar</button>
							<button type="button" class="btn-cancel">Cancelar</button>
						</div>
					</form>
				</div>
			`;

			document.body.appendChild(modalOverlay);

			const form = modalOverlay.querySelector('form');
			
			// Función para cerrar el modal
			const closeModal = () => {
				modalOverlay.remove();
			};

			// Manejar el envío del formulario
			form.addEventListener('submit', function(e) {
				e.preventDefault();
				// Intentar persistir en servidor si existe data-id
				const productId = row.getAttribute('data-id');
				const updateField = (field, value) => {
					const element = row.querySelector(`[data-field="${field}"]`);
					if (!element) return;

					if (field === 'precio') {
						element.textContent = '$' + value;
					} else if (field === 'existencia') {
						const badge = element.querySelector('.badge') || document.createElement('span');
						badge.className = `badge badge--${value.toLowerCase() === 'active' ? 'active' : 'out'}`;
						badge.textContent = value;
						if (!element.contains(badge)) {
							element.appendChild(badge);
						}
					} else if (field === 'product-name') {
						const nameText = element.querySelector('.product-name__text') || element;
						nameText.textContent = value;
					} else {
						element.textContent = value;
					}
				};

				const formData = new FormData(form);

				// Si no hay productId, sólo actualizar DOM (no persistir)
				if (!productId) {
					formData.forEach((value, field) => updateField(field, value));
					closeModal();
					return;
				}

				// Construir payload para servidor
				const payload = { id: productId };
				formData.forEach((value, field) => {
					// mapear nombres del form a nombres de campos en BD
					const map = {
						'product-name': 'nombre',
						'categoria': 'categoria',
						'codigo': 'codigo',
						'variante': 'variante',
						'precio': 'precio',
						'existencia': 'existencia',
						'stock': 'stock'
					};
					const key = map[field] || field;
					payload[key] = value;
				});

				// Enviar petición al backend
				// referencias a elementos del formulario para manejo UX
				const saveBtn = form.querySelector('.btn-save');
				const originalBtnText = saveBtn ? saveBtn.textContent : null;
				const setSaving = (saving) => {
					if (!saveBtn) return;
					saveBtn.disabled = saving;
					saveBtn.textContent = saving ? 'Guardando...' : originalBtnText;
				};

				// helper: mostrar toast simple
				const showToast = (message, ok = true) => {
					const toast = document.createElement('div');
					toast.className = 'simple-toast ' + (ok ? 'toast-success' : 'toast-error');
					toast.textContent = message;
					Object.assign(toast.style, {
						position: 'fixed',
						right: '20px',
						top: '20px',
						padding: '10px 14px',
						zIndex: 9999,
						borderRadius: '6px',
						color: '#fff',
						boxShadow: '0 2px 8px rgba(0,0,0,0.2)'
					});
					if (ok) toast.style.background = '#28a745'; else toast.style.background = '#dc3545';
					document.body.appendChild(toast);
					setTimeout(() => { toast.remove(); }, 3000);
				};

				setSaving(true);
				fetch('../inventario/update.php', {
					method: 'POST',
					headers: { 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
					body: new URLSearchParams(payload)
				})
				.then(async r => {
					const txt = await r.text();
					let json = null;
					try { json = JSON.parse(txt); } catch (e) { json = null; }
					if (!r.ok) {
						const errMsg = (json && json.error) ? json.error : ('HTTP ' + r.status);
						throw new Error(errMsg);
					}
					return json;
				})
				.then(res => {
					if (res && res.success) {
						formData.forEach((value, field) => updateField(field, value));
						showToast('Producto guardado', true);
						closeModal();
					} else {
						showToast('No se pudo guardar: ' + (res && res.error ? res.error : 'error desconocido'), false);
					}
				})
				.catch(err => {
					console.error('Error al actualizar producto:', err);
					showToast('Error: ' + (err.message || 'Error de red'), false);
				})
				.finally(() => setSaving(false));
			});

			// Cerrar modal con el botón de cancelar
			modalOverlay.querySelector('.btn-cancel').addEventListener('click', closeModal);

			// Cerrar modal al hacer clic fuera del contenido
			modalOverlay.addEventListener('click', function(e) {
				if (e.target === modalOverlay) {
					closeModal();
				}
			});

			// Enfocar el primer campo
			modalOverlay.querySelector('input').focus();

			return;
		}

		// Eliminar (ahora via AJAX al servidor)
		const delBtn = e.target.closest('.btn-delete');
		if (delBtn) {
			const row = delBtn.closest('tr.product-row');
			const name = row ? (row.querySelector('.product-name__text') || {}).textContent : '';
			if (!confirm('¿Eliminar "' + (name ? name.trim() : 'este producto') + '"?')) {
				return;
			}
			const productId = row ? row.getAttribute('data-id') : null;
			if (!productId) {
				// No id (no guardado en DB), solo eliminar del DOM
				if (row) row.remove();
				return;
			}
			// Cambiar a petición POST y esperar respuesta JSON
			console.info('Enviando petición a eliminar.php id=' + productId);
			fetch('../inventario/eliminar.php?format=json', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
				body: new URLSearchParams({ id: productId })
			})
			.then(async r => {
				const txt = await r.text();
				console.info('Respuesta raw de eliminar.php:', txt);
				let json = null;
				try { json = JSON.parse(txt); } catch (e) { json = null; }
				if (!r.ok) {
					const errMsg = (json && json.error) ? json.error : ('HTTP ' + r.status);
					throw new Error(errMsg);
				}
				return json;
			})
			.then(async json => {
				if (json && json.success) {
					// Verificación extra: comprobar que el producto ya no esté en listada por el servidor
					try {
						const listRes = await fetch('../inventario/listar.php?format=json');
						const listText = await listRes.text();
						let listJson = null;
						try { listJson = JSON.parse(listText); } catch (err) { listJson = null; }
						if (listJson && Array.isArray(listJson)) {
							const stillExists = listJson.some(p => String(p.id_producto || p.id || p.ID) === String(productId));
							if (stillExists) {
								throw new Error('Verificación: el producto sigue existiendo en la base de datos');
							}
						}
					} catch (err) {
						// Si la verificación falla, informar al usuario pero no tratar como éxito
						console.warn('Post-delete verification failed:', err);
						throw err;
					}
					if (row) row.remove();
					const toast = document.createElement('div');
					toast.className = 'simple-toast toast-success';
					toast.textContent = 'Producto eliminado';
					Object.assign(toast.style, {position: 'fixed', right:'20px', top:'20px', padding:'10px 14px', zIndex: 9999, borderRadius: '6px', color: '#fff', background: '#28a745', boxShadow: '0 2px 8px rgba(0,0,0,0.2)'});
					document.body.appendChild(toast);
					setTimeout(() => toast.remove(), 2000);
				} else {
					// Si existe un contador de registros relacionados (orden_detalle), preguntar si se desea forzar la eliminación
					if (json && typeof json.related !== 'undefined' && Number(json.related) > 0) {
						const ok = confirm('Existen ' + json.related + ' orden(es) relacionadas con este producto. ¿Desea eliminar igualmente (se eliminarán registros relacionados)?');
						if (ok) {
							// Reintentar con flag force=1
							return fetch('../inventario/eliminar.php?format=json', {
								method: 'POST',
								headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
								body: new URLSearchParams({ id: productId, force: 1 })
							}).then(async res2 => {
								const txt2 = await res2.text();
								let json2 = null;
								try { json2 = JSON.parse(txt2); } catch (e) { json2 = null; }
								if (!res2.ok || !(json2 && json2.success)) {
									const errMsg = (json2 && json2.error) ? json2.error : ('HTTP ' + res2.status + ' ' + txt2);
									throw new Error(errMsg);
								}
								return json2;
							});
						}
					}
					throw new Error((json && json.error) ? json.error : 'No se eliminó el producto');
				}
			})
			.catch(err => {
				console.error('Error al eliminar producto:', err);
				const toast = document.createElement('div');
				toast.className = 'simple-toast toast-error';
				toast.textContent = 'No se pudo eliminar: ' + (err.message || 'error');
				Object.assign(toast.style, {position: 'fixed', right:'20px', top:'20px', padding:'10px 14px', zIndex: 9999, borderRadius: '6px', color: '#fff', background: '#dc3545', boxShadow: '0 2px 8px rgba(0,0,0,0.2)'});
				document.body.appendChild(toast);
				setTimeout(() => toast.remove(), 3000);
			});
			return;
		}
	});



