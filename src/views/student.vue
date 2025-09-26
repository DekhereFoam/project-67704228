<template lang="">
  <div class="container mt-4">
    <h2 class="mb-3">รายชื่อลูกค้า</h2>

    <div class="mb-3">
      <a class="btn btn-primary" href="/add" role="button">Add+</a>
    </div>

    <!-- ตารางแสดงข้อมูลลูกค้า -->
    <table class="table table-bordered table-striped">
      <thead class="table-primary">
        <tr>
          <th>รหัสนักศึกษา</th>
          <th>ชื่อ</th>
          <th>นามสกุล</th>
          <th>เบอร์โทร</th>
          <th>อีเมล</th>
          <th>จบ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.student_id">
          <td>{{ student.student_id }}</td>
          <td>{{ student.first_name }}</td>
          <td>{{ student.last_name }}</td>
          <td>{{ student.phone }}</td>
          <td>{{ student.email }}</td>
          <!-- เพิ่มปุ่มลบ -->
          <td>
            <button class="btn btn-danger btn-sm" @click="deleteStudent(student.student_id)">ลบ</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Loading -->
    <div v-if="loading" class="text-center">
      <p>กำลังโหลดข้อมูล...</p>
    </div>

    <!-- Error -->
    <div v-if="error" class="alert alert-danger">
      {{ error }}
    </div>

  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "studentList",
  setup() {
    const students = ref([]);
    const loading = ref(true);
    const error = ref(null);

    // ฟังก์ชันดึงข้อมูลจาก API ด้วย GET
    const fetchStudents = async () => {
      try {
        loading.value = true;
        error.value = null;
        
        const response = await fetch("http://localhost:8081/project-67704228/api_php/show_student.php", {
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
          // ตรวจสอบว่า result.data เป็น array หรือไม่
          if (Array.isArray(result.data)) {
            students.value = result.data;
          } else {
            students.value = [];
            error.value = "ข้อมูลไม่ถูกต้อง";
          }
        } else {
          error.value = result.message || "เกิดข้อผิดพลาด";
        }
      } catch (err) {
        error.value = err.message;
        students.value = []; // ตั้งค่า fallback
      } finally {
        loading.value = false;
      }
    };

    onMounted(() => {
      fetchStudents();
    });

    const deleteStudent = async (id) => {
      if (!confirm("ยืนยันการลบข้อมูลนักเรียน?")) return;

      try {
        const response = await fetch("http://localhost:8081/project-67704228/api_php/api_student.php", {
          method: "DELETE",
          headers: {
            "Content-Type": "application/json"
          },
          body: JSON.stringify({ student_id: id })
        });

        const result = await response.json();

        if (result.success) {
          // ตรวจสอบ students.value ก่อนใช้ filter
          if (students.value && Array.isArray(students.value)) {
            students.value = students.value.filter(student => student.student_id !== id);
          }
          alert(result.message);
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

    return {
      students,
      loading,
      deleteStudent,
      error
    };
  }
};
</script>