// inicio.js — inicialización de la página de inicio: búsqueda, métricas y reactividad

function formatCurrency(value){
    return '$' + Number(value).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
}

function getRows(){
    const table = document.querySelector('.products-table');
    if(!table) return [];
    return Array.from(table.querySelectorAll('tbody tr'));
}

function computeMetrics(){
    const rows = getRows().filter(r => r.style.display !== 'none');
    const numProducts = rows.length;
    let totalStock = 0;
    let totalValue = 0;

    rows.forEach(r => {
        const stockEl = r.querySelector('[data-field="stock"]');
        const priceEl = r.querySelector('[data-field="precio"]');
        const stock = Number(stockEl ? stockEl.textContent.trim() : 0) || 0;
        let priceText = priceEl ? priceEl.textContent.trim() : '0';
        priceText = priceText.replace('$','').replace(/,/g,'').trim();
        const price = Number(priceText) || 0;
        totalStock += stock;
        totalValue += (price * stock);
    });

    const elProducts = document.getElementById('metricProducts');
    const elInStock = document.getElementById('metricInStock');
    const elValue = document.getElementById('metricValue');
    if(elProducts) elProducts.textContent = numProducts;
    if(elInStock) elInStock.textContent = totalStock;
    if(elValue) elValue.textContent = formatCurrency(totalValue);
}

function applySearch(filter){
    const q = String(filter || '').toLowerCase().trim();
    const rows = getRows();
    rows.forEach(r => {
        const name = (r.querySelector('[data-field="product-name"]')?.textContent || r.querySelector('.product-name__text')?.textContent || '').toLowerCase();
        const cat = (r.querySelector('[data-field="categoria"]')?.textContent || '').toLowerCase();
        const sku = (r.querySelector('[data-field="codigo"]')?.textContent || '').toLowerCase();
        const hay = name + ' ' + cat + ' ' + sku;
        const show = q === '' ? true : hay.indexOf(q) !== -1;
        r.style.display = show ? '' : 'none';
    });
    // recalcular métricas tras filtro
    computeMetrics();
}

function setupSearch(){
    const input = document.getElementById('searchInput');
    if(!input) return;
    let to = null;
    input.addEventListener('input', function(e){
        clearTimeout(to);
        to = setTimeout(() => applySearch(e.target.value), 150);
    });
}

function observeTableChanges(){
    const table = document.querySelector('.products-table tbody');
    if(!table) return;
    const mo = new MutationObserver(() => {
        // cuando cambian filas (agregar/eliminar/editar), recalcular métricas
        computeMetrics();
    });
    mo.observe(table, {childList:true, subtree:true, characterData:false});
}

function bindAddButton(){
    const add = document.querySelector('.btn-add');
    if(!add) return;
    add.addEventListener('click', function(){
        // showAddProductForm está definido en funciones/funcion.js
        if(typeof showAddProductForm === 'function'){
            showAddProductForm();
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    // inicializar comportamientos
    setupSearch();
    computeMetrics();
    observeTableChanges();
    bindAddButton();

    // si se carga la paginación desde funcion.js, esperar un tick y recalcular métricas
    setTimeout(computeMetrics, 350);
});
