<template lang="">
  <div class="container mt-4 col-md-4 bg-body-secondary">
    <h2 class="text-center mb-3">ลงทะเบียน</h2>
    
    <!-- Loading indicator -->
    <div v-if="loading" class="text-center mb-3">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p>กำลังบันทึกข้อมูล...</p>
    </div>

    <form @submit.prevent="addstudent" :class="{ 'd-none': loading }">
      <div class="mb-2">
        <input 
          v-model="student.first_name" 
          class="form-control" 
          placeholder="ชื่อ" 
          type="text"
          required 
          :disabled="loading"
        />
      </div>
      <div class="mb-2">
        <input 
          v-model="student.last_name" 
          class="form-control" 
          placeholder="นามสกุล" 
          type="text"
          required 
          :disabled="loading"
        />
      </div>
      <div class="mb-2">
        <input 
          v-model="student.phone" 
          class="form-control" 
          placeholder="เบอร์โทร (เช่น 0812345678)" 
          type="tel"
          pattern="[0-9]{10}"
          title="กรุณาใส่เบอร์โทร 10 หลัก"
          required 
          :disabled="loading"
        />
      </div>
      <div class="mb-2">
        <input 
          v-model="student.email" 
          class="form-control" 
          placeholder="อีเมล (เช่น example@email.com)" 
          type="email"
          required 
          :disabled="loading"
        />
      </div>
      <div class="text-center mt-4">
        <button 
          type="submit" 
          class="btn btn-primary mb-4" 
          :disabled="loading || !isFormValid"
        >
          {{ loading ? 'กำลังบันทึก...' : 'บันทึก' }}
        </button> &nbsp;
        <button 
          type="button" 
          @click="resetForm"
          class="btn btn-secondary mb-4"
          :disabled="loading"
        >
          ยกเลิก
        </button>
      </div>
    </form>

    <!-- Success message -->
    <div v-if="message && !error" class="alert alert-success mt-3">
      <i class="bi bi-check-circle"></i> {{ message }}
    </div>

    <!-- Error message -->
    <div v-if="error" class="alert alert-danger mt-3">
      <i class="bi bi-exclamation-triangle"></i> {{ error }}
    </div>

    <!-- Debug information (only in development) -->
    <div v-if="showDebug" class="alert alert-info mt-3">
      <h6>Debug Information:</h6>
      <p><strong>Sending data:</strong></p>
      <pre>{{ JSON.stringify(student, null, 2) }}</pre>
      <p><strong>Form valid:</strong> {{ isFormValid }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'StudentRegistration',
  data() {
    return {
      student: {
        first_name: "",
        last_name: "",
        phone: "",
        email: ""
      },
      message: "",
      error: "",
      loading: false,
      showDebug: false // Set to true for debugging
    };
  },
  computed: {
    isFormValid() {
      return this.student.first_name.trim() && 
             this.student.last_name.trim() && 
             this.student.phone.trim() && 
             this.student.email.trim() &&
             this.isValidEmail(this.student.email) &&
             this.isValidPhone(this.student.phone);
    }
  },
  methods: {
    isValidEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email);
    },
    
    isValidPhone(phone) {
      const phoneRegex = /^[0-9]{10}$/;
      return phoneRegex.test(phone.replace(/[-\s]/g, ''));
    },
    
    async addstudent() {
      this.loading = true;
      this.message = "";
      this.error = "";

      // Client-side validation
      if (!this.isFormValid) {
        this.error = "กรุณากรอกข้อมูลให้ถูกต้องและครบถ้วน";
        this.loading = false;
        return;
      }

      try {
        // Clean phone number (remove spaces and dashes)
        const cleanedStudent = {
          ...this.student,
          phone: this.student.phone.replace(/[-\s]/g, ''),
          email: this.student.email.toLowerCase().trim()
        };

        console.log("Sending data:", cleanedStudent); // Debug log

        const response = await fetch("http://localhost:8081/project-67704228/api_php/add_student.php", {
          method: "POST",
          headers: { 
            "Content-Type": "application/json",
            "Accept": "application/json"
          },
          body: JSON.stringify(cleanedStudent)
        });

        // Check if response is ok
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log("Response data:", data); // Debug log

        if (data.success) {
          this.message = data.message || "เพิ่มข้อมูลนักเรียนสำเร็จ!";
          this.resetForm();
          
          // Auto-hide success message after 5 seconds
          setTimeout(() => {
            this.message = "";
          }, 5000);
        } else {
          this.error = data.message || "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }

      } catch (err) {
        console.error("Error:", err);
        this.error = "เกิดข้อผิดพลาดในการเชื่อมต่อ: " + err.message;
        
        // Additional error handling for common issues
        if (err.message.includes('Failed to fetch')) {
          this.error = "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต";
        } else if (err.message.includes('CORS')) {
          this.error = "ปัญหาการเชื่อมต่อ CORS กรุณาติดต่อผู้ดูแลระบบ";
        }
      } finally {
        this.loading = false;
      }
    },

    resetForm() {
      this.student = {
        first_name: "",
        last_name: "",
        phone: "",
        email: ""
      };
      this.message = "";
      this.error = "";
    },

    // Format phone number as user types
    formatPhone() {
      let phone = this.student.phone.replace(/\D/g, '');
      if (phone.length > 10) {
        phone = phone.substring(0, 10);
      }
      this.student.phone = phone;
    }
  },

  watch: {
    // Watch phone input and format it
    'student.phone'(newVal) {
      // Remove non-digits and limit to 10 digits
      const cleaned = newVal.replace(/\D/g, '').substring(0, 10);
      if (cleaned !== newVal) {
        this.$nextTick(() => {
          this.student.phone = cleaned;
        });
      }
    },

    // Clear error when user starts typing
    'student.first_name'() { this.error = ""; },
    'student.last_name'() { this.error = ""; },
    'student.phone'() { this.error = ""; },
    'student.email'() { this.error = ""; }
  },

  mounted() {
    // Focus on first input when component mounts
    this.$nextTick(() => {
      const firstInput = this.$el.querySelector('input[type="text"]');
      if (firstInput) {
        firstInput.focus();
      }
    });
  }
};
</script>

<style scoped>
.container {
  max-width: 400px;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.form-control:focus {
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinner-border {
  width: 2rem;
  height: 2rem;
}

.alert {
  border-radius: 8px;
}

pre {
  font-size: 0.8rem;
  background: #f8f9fa;
  padding: 0.5rem;
  border-radius: 4px;
}

@media (max-width: 576px) {
  .container {
    margin: 1rem;
    max-width: calc(100% - 2rem);
  }
}
</style>