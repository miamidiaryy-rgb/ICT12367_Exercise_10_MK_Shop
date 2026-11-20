<script setup>
import { ref } from 'vue'
import CustomerMenu from './components/CustomerMenu.vue'
import ProductManager from './components/ProductManager.vue'
import CategoryManager from './components/CategoryManager.vue'

const activePage = ref('menu') // 'menu' | 'products' | 'categories'
</script>

<template>
  <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #7ec0ee">
    <div class="container">
      <a class="navbar-brand fw-semibold" href="#" @click.prevent="activePage = 'menu'">MK Shop</a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mkNav"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mkNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: activePage === 'menu', 'fw-semibold': activePage === 'menu' }"
              @click.prevent="activePage = 'menu'"
            >
              เมนูสินค้า (ลูกค้า)
            </a>
          </li>
          <li class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: activePage === 'products', 'fw-semibold': activePage === 'products' }"
              @click.prevent="activePage = 'products'"
            >
              จัดการสินค้า
            </a>
          </li>
          <li class="nav-item">
            <a
              href="#"
              class="nav-link"
              :class="{ active: activePage === 'categories', 'fw-semibold': activePage === 'categories' }"
              @click.prevent="activePage = 'categories'"
            >
              ประเภทสินค้า
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <CustomerMenu v-if="activePage === 'menu'" />
  <ProductManager v-else-if="activePage === 'products'" />
  <CategoryManager v-else />
</template>

<style>
body {
  font-family: 'Leelawadee UI', Tahoma, sans-serif;
  background-color: #ffffff;
}
</style>
