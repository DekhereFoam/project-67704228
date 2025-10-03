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
          <th>แก้ไข</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in students" :key="student.student_id">
          <td>{{ student.student_id }}</td>
          <td>{{ student.first_name }}</td>
          <td>{{ student.last_name }}</td>
          <td>{{ student.phone }}</td>
          <td>{{ student.email }}</td>
         <td>
            <!-- เพิ่ม ปุ่มแก้ไข -->
            <button class="btn btn-warning btn-sm" @click="openEditModal(student)"><i class="fa-solid fa-pen-to-square"></i>แก้ไข</button> |      
            <!-- ปุ่มลบ -->
            <button class="btn btn-danger btn-sm" @click="deleteStudent(student.student_id)"><i class="fa-solid fa-trash"></i>ลบ</button>
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

    <!-- เพิ่ม Modal แก้ไขข้อมูล -->
    <div class="modal fade" id="editModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">แก้ไขข้อมูลนักเรียน</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateStudent">
              <div class="mb-3">
                <label class="form-label">ชื่อ</label>
                <input v-model="editStudent.first_name" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">นามสกุล</label>
                <input v-model="editStudent.last_name" type="text" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">อีเมล</label>
                <input v-model="editStudent.email" type="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">เบอร์โทร</label>
                <input v-model="editStudent.phone" type="text" class="form-control" required>
              </div>
              <div class="mb-3 text-center">
              <button type="submit" class="btn btn-success">บันทึก</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>




  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import { Modal } from "bootstrap"; 

export default {
  name: "StudentList",
  setup() {
    const students = ref([]);
    const loading = ref(true);
    const error = ref(null);
     const editStudent = ref({});   //เพิ่ม
    let editModal;                  //เพิ่ม

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
       const modalEl = document.getElementById("editModal");     //เพิ่ม
      editModal = new Modal(modalEl);   // เพิ่ม ✅ ใช้ Modal ที่ import มา
    });



//เพิ่ม เปิด Popup Modal ***
    const openEditModal = (student) => {
      editStudent.value = { ...student };
      editModal.show();
    };
// เพิ่มฟังก์ชั่นการแก้ไขข้อมูล ***
    const updateStudent = async () => {
      try {
        const response = await fetch("http://localhost:8081/project-67704228/api_php/api_student.php", {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(editStudent.value)
        });

        const result = await response.json();

        if (result.success) {
          const index = students.value.findIndex(c => c.student_id === editStudent.value.Student_id);
          if (index !== -1) students.value[index] = { ...editStudent.value };

          alert("แก้ไขข้อมูลสำเร็จ");
          editModal.hide();
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert("เกิดข้อผิดพลาด: " + err.message);
      }
    };

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
      error ,
      editStudent,  //เพิ่ม
      openEditModal,  //เพิ่ม
      updateStudent  //เพิ่ม
    };
  }
};
</script>