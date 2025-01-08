function convertSolarToLunar() {
    const solarDate = document.getElementById('solarDate').value;
    const result = document.getElementById('lunarResult');
    
    if (!solarDate) {
        result.textContent = 'Vui lòng chọn ngày';
        return;
    }

    // Tạm thời hiển thị kết quả mẫu
    // Sẽ cập nhật sau khi thêm thư viện chuyển đổi âm lịch
    const date = new Date(solarDate);
    result.textContent = `Ngày ${date.getDate()} tháng ${date.getMonth() + 1} năm ${date.getFullYear()} (Âm lịch)`;
} 