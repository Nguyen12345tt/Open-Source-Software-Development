# Lab 02 — Xây dựng module php kiểm tra điều kiện đăng ký

Mã mẫu cho phần hướng dẫn và mã khởi đầu cho bài tự làm.

## Mở bài

Mở terminal Ubuntu/WSL tại thư mục chứa `lab.sh`. Dùng PHP từ 8.1 và Git. Hướng dẫn chi tiết, sơ đồ, đoạn mã cần sửa, dữ liệu đối chiếu và rubric nằm trong tài liệu của bài lab.

```bash
bash lab.sh check
bash lab.sh run
```

Chương trình in kết quả ngay trong terminal.

Bài này không cần PostgreSQL, Composer hoặc framework.

## Kiểm tra bài

```bash
bash lab.sh test
bash lab.sh extend
```

`test` kiểm tra phần mẫu; `extend` kiểm tra phần tự làm. Các vị trí TODO là nhiệm vụ cần hoàn thiện; không sửa test để bỏ qua lỗi.

Kết quả mở rộng cần đạt: `TESTS=8 FAILED=0`. Có lớp kiểm tra sức chứa, chương trình gọi lớp và lớp mở rộng trả confirmed, waiting hoặc rejected theo quy tắc đã công bố.

## Tệp nộp

Giữ mã nguồn, dữ liệu mẫu và kiểm thử. Ghi lại lệnh chạy trong README. Không đưa `.local`, mật khẩu hoặc dữ liệu cá nhân thật vào kho mã và gói nộp. Nộp đúng commit theo yêu cầu của bài; nếu dùng Actions, đính kèm lần chạy tương ứng.

## Ghi chú thực hành và kết quả đối chiếu

### 1. Các lệnh chạy và kiểm thử trực tiếp

```bash
# Kiểm tra cú pháp PHP
php -l thu-ham.php
php -l src/CapacityPolicy.php
php -l src/RegistrationPolicy.php
php -l demo.php

# Chạy thử hàm và chương trình minh họa
php thu-ham.php
php demo.php

# Chạy bộ kiểm thử qua lab.sh
bash lab.sh check
bash lab.sh run
bash lab.sh test
bash lab.sh extend
```

### 2. Quy tắc và giải thích phần mẫu (`CapacityPolicy` & `demo.php`)

- **Quy tắc bằng lời:** Sức chứa (`$capacity`) phải lớn hơn `0`; số người đã đăng ký (`$registered`) không được âm (`>= 0`); sự kiện chỉ còn chỗ khi số đã đăng ký nhỏ hơn sức chứa (`$registered < $capacity`). Nếu số đã đăng ký bằng hoặc lớn hơn sức chứa thì không cho đăng ký tiếp.
- **Luồng mã:** `demo.php` nạp lớp `src/CapacityPolicy.php` bằng `require`, khởi tạo đối tượng `new CapacityPolicy()`, gọi `$policy->canRegister(10, 9)` để nhận giá trị kiểu `bool`, sau đó dùng câu lệnh điều kiện `if ($available)` để in `AVAILABLE` hoặc `FULL`. Lớp nghiệp vụ không chứa HTML, `echo`, Session hay CSDL.
- **Kiểm thử hồi quy (đổi `<` thành `<=`):** Khi tạm thời đổi `$registered < $capacity` thành `$registered <= $capacity`, ca kiểm thử `full` với đầu vào `(10, 10)` báo `FAIL full expected=false actual=true` vì sự kiện đã đủ 10/10 chỗ nhưng vẫn cho phép đăng ký thêm người thứ 11.

#### Bảng đối chiếu dữ liệu thử — `CapacityPolicy::canRegister()`

| Đầu vào (`$capacity`, `$registered`) | Tình huống | Kết quả yêu cầu (`canRegister` / `demo.php`) | Kết quả thực tế |
| --- | --- | --- | --- |
| `(10, 9)` | Đúng (còn 1 chỗ) | `true` / `AVAILABLE` | `true` / `AVAILABLE` |
| `(10, 10)` | Biên (vừa đầy chỗ) | `false` / `FULL` | `false` / `FULL` |
| `(10, 11)` | Sai (vượt sức chứa) | `false` / `FULL` | `false` / `FULL` |
| `(0, 0)` | Biên sai (sức chứa bằng 0) | `false` / `FULL` | `false` / `FULL` |
| `(10, -1)` | Sai (số đã đăng ký âm) | `false` / `FULL` | `false` / `FULL` |

### 3. Giải thích bài tập mở rộng (`RegistrationPolicy::decide()`)

- **Thứ tự ưu tiên kiểm tra trong `decide(int $capacity, int $registered, bool $open, bool $waitingEnabled): string`:**
  1. **Kiểm tra dữ liệu hợp lệ:** Nếu `$capacity <= 0` hoặc `$registered < 0` hoặc `$registered > $capacity` $\rightarrow$ trả về `'rejected'`.
  2. **Kiểm tra trạng thái mở của sự kiện:** Nếu `!$open` (sự kiện đang đóng) $\rightarrow$ trả về `'rejected'` (kể cả khi còn chỗ hoặc bật danh sách chờ).
  3. **Kiểm tra còn chỗ:** Nếu `$registered < $capacity` $\rightarrow$ trả về `'confirmed'`.
  4. **Xử lý khi đầy chỗ (`$registered === $capacity`):** Nếu `$waitingEnabled` là `true` $\rightarrow$ trả về `'waiting'`; ngược lại trả về `'rejected'`.
- **Phần nâng cao (mã lý do):** Bổ sung thuộc tính `$lastReason` cùng phương thức `getLastReason(): string` và `decideWithReason(...): array` trả về mã lý do chi tiết (`invalid_capacity`, `invalid_registered_count`, `event_closed`, `seat_available`, `waitlist_enabled`, `full_no_waitlist`) mà vẫn giữ nguyên chữ ký và kiểu trả về `string` của `decide()`.
- **Ca kiểm thử bổ sung (`closed-when-full`):**
  - Lời gọi: `same($p->decide(10, 10, false, true), 'rejected', 'closed-when-full');`
  - **Mục đích:** Kiểm tra thứ tự ưu tiên giữa trạng thái sự kiện đóng (`$open = false`) và danh sách chờ (`$waitingEnabled = true`) tại biên đầy chỗ (`10/10`). Nếu kiểm tra đầy chỗ + danh sách chờ trước khi kiểm tra trạng thái đóng, chương trình sẽ trả về sai là `'waiting'` thay vì `'rejected'`.

#### Bảng đối chiếu dữ liệu thử — `RegistrationPolicy::decide()`

| Ca kiểm thử | Đầu vào (`$capacity, $registered, $open, $waitingEnabled`) | Kết quả mong đợi | Kết quả thực tế | Mã lý do (`getLastReason()`) |
| --- | --- | --- | --- | --- |
| `available` | `(10, 9, true, true)` | `'confirmed'` | `'confirmed'` | `'seat_available'` |
| `full-wait` | `(10, 10, true, true)` | `'waiting'` | `'waiting'` | `'waitlist_enabled'` |
| `full-no-wait` | `(10, 10, true, false)` | `'rejected'` | `'rejected'` | `'full_no_waitlist'` |
| `closed` | `(10, 2, false, true)` | `'rejected'` | `'rejected'` | `'event_closed'` |
| `invalid-capacity` | `(0, 0, true, true)` | `'rejected'` | `'rejected'` | `'invalid_capacity'` |
| `invalid-count` | `(10, -1, true, true)` | `'rejected'` | `'rejected'` | `'invalid_registered_count'` |
| `corrupt-over-count` | `(10, 11, true, true)` | `'rejected'` | `'rejected'` | `'invalid_registered_count'` |
| `first-seat` | `(1, 0, true, false)` | `'confirmed'` | `'confirmed'` | `'seat_available'` |
| `closed-when-full` *(bổ sung)* | `(10, 10, false, true)` | `'rejected'` | `'rejected'` | `'event_closed'` |

