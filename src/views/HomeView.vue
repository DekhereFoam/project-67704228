<template>
  <div class="home">
    <div class="container">
  <h1 class="text-primary">Hello Bootstrap 5 </h1>
    
    <HelloWorld msg="Welcome to Your Vue.js App"/>
    </div>
  </div>
</template>

<script>
// @ is an alias to /src
import HelloWorld from '@/components/HelloWorld.vue'

export default {
  name: 'HomeView',
  components: {
    HelloWorld
  }
}

</script>
