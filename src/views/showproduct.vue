<template>
  <div class="container my-5">
    <h2 class="text-center mb-4">รายการสินค้า</h2>
    <div class="row">
      <div class="col-md-4" v-for="product in products" :key="product.product_id">
                <div class="card shadow-sm mb-4">
          <img :src="'http://localhost:8081/project-67704228/api_php/upload/' + product.image" width="70%" height="300" 
           class="card-img-top" :alt="product.name">
          <div class="card-body text-center">
            <h5 class="card-title">{{ product.product_name }}</h5>
            <p class="card-text">ราคา {{ product.price }} บาท</p>
            <p class="card-text">คงเหลือ {{ product.stock }} จำนวน</p>
            <button class="btn btn-primary">รายละเอียด</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "ProductList",
  setup() {
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);

    // ฟังก์ชันดึงข้อมูลจาก API ด้วย GET
    const fetchProducts = async () => {
      try {
        const response = await fetch("http://localhost:8081/project-67704228/api_php/show_product.php", {
          method: "GET",
          headers: {
            "Content-Type": "application/json"
          }
        });

        if (!response.ok) {
          throw new Error("ไม่สามารถดึงข้อมูลได้");
        }

        const result = await response.json();
        if (result.success) {
          products.value = result.data;
        } else {
          error.value = result.message;
        }

      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchProducts();
    });

    return {
      products,
      loading,
      error
    };
  }
};
</script>
