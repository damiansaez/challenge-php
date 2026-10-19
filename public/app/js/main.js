import { ApiError, createProduct, deleteProduct, listProducts, updateProduct } from './api.js';

const form = document.getElementById('product-form');
const formTitle = document.getElementById('form-title');
const submitButton = document.getElementById('submit-button');
const cancelButton = document.getElementById('cancel-button');
const productList = document.getElementById('product-list');
const message = document.getElementById('message');

const arsFormat = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' });
const usdFormat = new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'USD' });

let products = [];
let editingId = null;
let messageTimer = null;

function showMessage(text, type) {
    clearTimeout(messageTimer);
    message.textContent = text;
    message.className = `message ${type}`;
    message.hidden = false;
    messageTimer = setTimeout(() => {
        message.hidden = true;
    }, 5000);
}

function showError(error) {
    showMessage(error instanceof ApiError ? error.message : 'Ocurrió un error inesperado', 'error');
}

function showFieldErrors(details) {
    for (const [field, text] of Object.entries(details)) {
        const errorElement = form.querySelector(`[data-error-for="${field}"]`);
        if (errorElement) {
            errorElement.textContent = text;
            form.elements[field]?.setAttribute('aria-invalid', 'true');
        }
    }
}

function clearFieldErrors() {
    form.querySelectorAll('.field-error').forEach((element) => {
        element.textContent = '';
    });
    form.querySelectorAll('[aria-invalid]').forEach((element) => {
        element.removeAttribute('aria-invalid');
    });
}

function createCell(text, className = '') {
    const cell = document.createElement('td');
    cell.textContent = text;
    if (className) {
        cell.className = className;
    }
    return cell;
}

function createButton(text, className, onClick) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = text;
    button.className = className;
    button.addEventListener('click', onClick);
    return button;
}

function renderProducts() {
    productList.replaceChildren();

    if (products.length === 0) {
        const row = document.createElement('tr');
        const cell = createCell('No hay productos cargados', 'empty');
        cell.colSpan = 5;
        row.append(cell);
        productList.append(row);
        return;
    }

    for (const product of products) {
        const row = document.createElement('tr');

        const buttons = document.createElement('div');
        buttons.className = 'row-actions';
        buttons.append(
            createButton('Editar', '', () => startEditing(product)),
            createButton('Eliminar', 'danger', () => removeProduct(product)),
        );

        const actions = document.createElement('td');
        actions.append(buttons);

        row.append(
            createCell(product.nombre),
            createCell(product.descripcion ?? '—'),
            createCell(arsFormat.format(Number(product.precio)), 'number'),
            createCell(usdFormat.format(Number(product.precio_usd)), 'number'),
            actions,
        );

        productList.append(row);
    }
}

function renderLoading() {
    productList.replaceChildren();
    const row = document.createElement('tr');
    const cell = createCell('Cargando productos...', 'empty');
    cell.colSpan = 5;
    row.append(cell);
    productList.append(row);
}

async function loadProducts() {
    renderLoading();

    try {
        products = await listProducts();
        renderProducts();
    } catch (error) {
        products = [];
        productList.replaceChildren();
        showError(error);
    }
}

function readForm() {
    const descripcion = form.elements.descripcion.value.trim();

    return {
        nombre: form.elements.nombre.value.trim(),
        descripcion: descripcion === '' ? null : descripcion,
        precio: form.elements.precio.value,
    };
}

function startEditing(product) {
    editingId = product.id;
    form.elements.nombre.value = product.nombre;
    form.elements.descripcion.value = product.descripcion ?? '';
    form.elements.precio.value = product.precio;

    formTitle.textContent = `Editar producto: ${product.nombre}`;
    submitButton.textContent = 'Guardar cambios';
    cancelButton.hidden = false;
    clearFieldErrors();
    form.elements.nombre.focus();
}

function resetForm() {
    editingId = null;
    form.reset();
    formTitle.textContent = 'Agregar producto';
    submitButton.textContent = 'Agregar';
    cancelButton.hidden = true;
    clearFieldErrors();
}

async function handleSubmit(event) {
    event.preventDefault();
    clearFieldErrors();
    submitButton.disabled = true;

    try {
        const product = readForm();

        if (editingId === null) {
            await createProduct(product);
            showMessage('Producto agregado correctamente', 'success');
        } else {
            await updateProduct(editingId, product);
            showMessage('Producto actualizado correctamente', 'success');
        }

        resetForm();
        await loadProducts();
    } catch (error) {
        if (error instanceof ApiError && error.status === 422) {
            showFieldErrors(error.details);
        }
        showError(error);
    } finally {
        submitButton.disabled = false;
    }
}

async function removeProduct(product) {
    if (!confirm(`¿Eliminar el producto "${product.nombre}"?`)) {
        return;
    }

    try {
        await deleteProduct(product.id);
        if (editingId === product.id) {
            resetForm();
        }
        showMessage('Producto eliminado correctamente', 'success');
        await loadProducts();
    } catch (error) {
        showError(error);
        if (error instanceof ApiError && error.status === 404) {
            await loadProducts();
        }
    }
}

form.addEventListener('submit', handleSubmit);
cancelButton.addEventListener('click', resetForm);

loadProducts();