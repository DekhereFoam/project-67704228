<template>
  <section id="menu" class="py-5">
    <div class="container">
      <h2 class="text-center mb-4 fw-bold">เมนูแนะนำ</h2>
      <div class="row g-4">
        <div class="col-md-4" v-for="item in menu" :key="item.id">
          <div class="card h-100 shadow-sm">
            <img :src="item.image" class="card-img-top" :alt="item.name" />
            <div class="card-body text-center">
              <h5 class="card-title">{{ item.name }}</h5>
              <p class="card-text text-muted">{{ item.description }}</p>
              <p class="fw-bold text-danger">{{ item.price }} บาท</p>
              <button class="btn btn-outline-danger btn-sm">สั่งเลย</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: "Menu",
  data() {
    return {
      menu: [
        { id: 1, name: "ชุดหมูสไลด์", description: "หมูสไลด์บางนุ่ม ละลายในปาก", price: 199, image: "https://peachyfood.net/Images/Products/Large/9911679-20230420-144740.jpg" },
        { id: 2, name: "ชุดเนื้อวัวพรีเมียม", description: "เนื้อวัวเกรดพรีเมียม สดใหม่ทุกวัน", price: 299, image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTi_cyM4vz0WHYR8tC-7HWIHWaVDARJ2LyB8g&s" },
        { id: 3, name: "ชุดทะเลรวม", description: "กุ้ง หมึก หอย ครบชุด สดจากทะเล", price: 249, image: "https://media-cdn.tripadvisor.com/media/photo-s/19/2a/cf/53/2.jpg" }
      ]
    };
  }
};
</script>
