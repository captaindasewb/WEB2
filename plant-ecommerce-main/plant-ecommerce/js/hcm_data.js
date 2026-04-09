const hcmData = [
  {
    "name": "Quận 1",
    "wards": ["Phường Tân Định", "Phường Đa Kao", "Phường Bến Nghé", "Phường Bến Thành", "Phường Nguyễn Thái Bình", "Phường Phạm Ngũ Lão", "Phường Cầu Ông Lãnh", "Phường Cô Giang", "Phường Nguyễn Cư Trinh", "Phường Cầu Kho"]
  },
  {
    "name": "Quận 3",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường Võ Thị Sáu"]
  },
  {
    "name": "Quận 4",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 6", "Phường 8", "Phường 9", "Phường 10", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 18"]
  },
  {
    "name": "Quận 5",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"]
  },
  {
    "name": "Quận 6",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"]
  },
  {
    "name": "Quận 7",
    "wards": ["Phường Tân Thuận Đông", "Phường Tân Thuận Tây", "Phường Tân Kiểng", "Phường Tân Hưng", "Phường Bình Thuận", "Phường Tân Quy", "Phường Phú Thuận", "Phường Tân Phú", "Phường Tân Phong", "Phường Phú Mỹ"]
  },
  {
    "name": "Quận 8",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"]
  },
  {
    "name": "Quận 10",
    "wards": ["Phường 1", "Phường 2", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"]
  },
  {
    "name": "Quận 11",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"]
  },
  {
    "name": "Quận 12",
    "wards": ["Phường Thạnh Xuân", "Phường Thạnh Lộc", "Phường Hiệp Thành", "Phường Thới An", "Phường Tân Chánh Hiệp", "Phường An Phú Đông", "Phường Tân Thới Hiệp", "Phường Trung Mỹ Tây", "Phường Tân Hưng Thuận", "Phường Đông Hưng Thuận", "Phường Tân Thới Nhất"]
  },
  {
    "name": "Quận Bình Tân",
    "wards": ["Phường Bình Hưng Hòa", "Phường Bình Hưng Hòa A", "Phường Bình Hưng Hòa B", "Phường Bình Trị Đông", "Phường Bình Trị Đông A", "Phường Bình Trị Đông B", "Phường Tân Tạo", "Phường Tân Tạo A", "Phường An Lạc", "Phường An Lạc A"]
  },
  {
    "name": "Quận Bình Thạnh",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 6", "Phường 7", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 17", "Phường 19", "Phường 21", "Phường 22", "Phường 24", "Phường 25", "Phường 26", "Phường 27", "Phường 28"]
  },
  {
    "name": "Quận Gò Vấp",
    "wards": ["Phường 1", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 17"]
  },
  {
    "name": "Quận Phú Nhuận",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 13", "Phường 15", "Phường 17"]
  },
  {
    "name": "Quận Tân Bình",
    "wards": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"]
  },
  {
    "name": "Quận Tân Phú",
    "wards": ["Phường Tân Sơn Nhì", "Phường Tây Thạnh", "Phường Sơn Kỳ", "Phường Tân Quý", "Phường Tân Thành", "Phường Phú Thọ Hòa", "Phường Phú Thạnh", "Phường Phú Trung", "Phường Hòa Thạnh", "Phường Hiệp Tân", "Phường Tân Thới Hòa"]
  },
  {
    "name": "Thành phố Thủ Đức",
    "wards": ["Phường Linh Xuân", "Phường Bình Chiểu", "Phường Linh Trung", "Phường Tam Bình", "Phường Tam Phú", "Phường Hiệp Bình Phước", "Phường Hiệp Bình Chánh", "Phường Linh Chiểu", "Phường Linh Tây", "Phường Linh Đông", "Phường Bình Thọ", "Phường Trường Thọ", "Phường Long Bình", "Phường Long Thạnh Mỹ", "Phường Tân Phú", "Phường Hiệp Phú", "Phường Tăng Nhơn Phú A", "Phường Tăng Nhơn Phú B", "Phường Phước Long A", "Phường Phước Long B", "Phường Trường Thạnh", "Phường Long Phước", "Phường Long Trường", "Phường Phú Hữu", "Phường Phước Bình", "Phường An Phú", "Phường Thảo Điền", "Phường An Khánh", "Phường Bình Trưng Đông", "Phường Bình Trưng Tây", "Phường Cát Lái", "Phường Thạnh Mỹ Lợi", "Phường An Lợi Đông", "Phường Thủ Thiêm"]
  },
  {
    "name": "Huyện Bình Chánh",
    "wards": ["Thị trấn Tân Túc", "Xã Phạm Văn Hai", "Xã Vĩnh Lộc A", "Xã Vĩnh Lộc B", "Xã Bình Lợi", "Xã Lê Minh Xuân", "Xã Tân Nhựt", "Xã Tân Kiên", "Xã Bình Hưng", "Xã Phong Phú", "Xã An Phú Tây", "Xã Hưng Long", "Xã Đa Phước", "Xã Tân Quý Tây", "Xã Bình Chánh", "Xã Quy Đức"]
  },
  {
    "name": "Huyện Cần Giờ",
    "wards": ["Thị trấn Cần Thạnh", "Xã Bình Khánh", "Xã Tam Thôn Hiệp", "Xã An Thới Đông", "Xã Thạnh An", "Xã Long Hòa", "Xã Lý Nhơn"]
  },
  {
    "name": "Huyện Củ Chi",
    "wards": ["Thị trấn Củ Chi", "Xã Phú Mỹ Hưng", "Xã An Phú", "Xã Trung Lập Thượng", "Xã An Nhơn Tây", "Xã Nhuận Đức", "Xã Phạm Văn Cội", "Xã Phú Hòa Đông", "Xã Trung Lập Hạ", "Xã Trung An", "Xã Phước Thạnh", "Xã Phước Hiệp", "Xã Tân An Hội", "Xã Phước Vĩnh An", "Xã Thái Mỹ", "Xã Tân Thạnh Tây", "Xã Hòa Phú", "Xã Tân Thạnh Đông", "Xã Bình Mỹ", "Xã Tân Phú Trung", "Xã Tân Thông Hội"]
  },
  {
    "name": "Huyện Hóc Môn",
    "wards": ["Thị trấn Hóc Môn", "Xã Tân Hiệp", "Xã Nhị Bình", "Xã Đông Thạnh", "Xã Thới Tam Thôn", "Xã Tân Xuân", "Xã Xuân Thới Sơn", "Xã Xuân Thới Thượng", "Xã Xuân Thới Đông", "Xã Trung Chánh", "Xã Bà Điểm"]
  },
  {
    "name": "Huyện Nhà Bè",
    "wards": ["Thị trấn Nhà Bè", "Xã Phước Kiển", "Xã Phước Lộc", "Xã Nhơn Đức", "Xã Phú Xuân", "Xã Long Thới", "Xã Hiệp Phước"]
  }
];
