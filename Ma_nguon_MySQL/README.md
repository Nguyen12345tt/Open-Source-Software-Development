# Lab 05 — Lưu, truy vấn và thống kê đăng ký với MySQL

Đây là lựa chọn MySQL của Lab 05. Dùng PHP >= 8.1, MySQL >= 8.0.16, MySQLi và mysqlnd trên Ubuntu/WSL. Không cần cài PostgreSQL để làm bản này.

Đọc [hướng dẫn MySQL đầy đủ](../HUONG_DAN_MYSQL.md) để cài công cụ, tạo tài khoản/CSDL riêng và học từng bước. Bản PostgreSQL vẫn nằm trong `../Ma_nguon`.

Mở terminal Ubuntu/WSL tại thư mục chứa `lab.sh`, sau khi tạo CSDL và tài khoản theo hướng dẫn:

```bash
bash lab.sh db-config
bash lab.sh check
bash lab.sh sql
```

Client SQL hỏi lại mật khẩu. Trong client:

```sql
source database/schema.sql;
SELECT id, title FROM events ORDER BY id;
exit
```

Trở về Bash:

```bash
php demo-db.php
bash lab.sh run
```

Mở http://127.0.0.1:8105/register.php. Giữ terminal máy chủ mở, Ctrl+C để dừng. Chỉ chạy một phiên bản lab trên cổng 8105 tại một thời điểm. Dùng terminal thứ hai ở cùng thư mục để kiểm thử:

```bash
bash lab.sh test
bash lab.sh extend
```

`test` cần `TESTS=6 FAILED=0`, `TESTS=7 FAILED=0` và `TESTS=9 FAILED=0`; `extend` cần `TESTS=4 FAILED=0` sau khi sinh viên tự hoàn thiện TODO trong `src/RegistrationStats.php`. `NOT_RUN` là chưa kiểm thử được. Không sửa test để bỏ qua lỗi.

Ghi rõ lựa chọn MySQL và phiên bản PHP/MySQL trong bài nộp. Giữ mã nguồn, schema, dữ liệu mẫu và test; không đưa `.local`, mật khẩu hoặc dữ liệu cá nhân thật vào Git/ZIP. Không dùng cấu hình hoặc schema PostgreSQL cho bản này.

Giao diện, form, CSRF, Session/Cookie và khu UI demo giống bản PostgreSQL. Các trang `ui-demo.php`/`ticket-preview.php` dùng dữ liệu hư cấu, không ghi CSDL và không chứng minh đăng ký thật. Mẫu chưa kiểm soát giới hạn chỗ khi nhiều request đăng ký đồng thời.

---

## Thông tin bài nộp & Kết quả thực hiện (MySQL)

### 1. Môi trường và lựa chọn hệ quản trị

- **Hệ quản trị CSDL đã chọn:** MySQL (`Ma_nguon_MySQL`)
- **Phiên bản PHP:** PHP 8.5.11 (cli) với `mysqli` và `mysqlnd`
- **Phiên bản MySQL:** MySQL Server 9.7.1 (`utf8mb4`, `utf8mb4_bin`, engine `InnoDB`)
- **CSDL thực hành riêng:** `communitypass_mysql_v5` (tài khoản `communitypass_mysql_v5@127.0.0.1`, không dùng `root` trong ứng dụng, không lưu `.local` vào Git).

### 2. Kiểm chứng Schema, Demo và Lưu bền vững qua Web

- **Nạp `database/schema.sql` và kiểm tra 3 sự kiện mẫu:**
  ```sql
  SELECT id, title FROM events ORDER BY id;
  -- 1 | PHP Open Day
  -- 2 | Design Workshop
  -- 3 | Git Clinic
  ```
- **Chạy `php demo-db.php` hai lần liên tiếp:**
  - Lần 1: `CREATED` (lưu `O'Neil Demo` với dấu nháy đơn được bảo toàn nguyên vẹn).
  - Lần 2: `DUPLICATE` (cùng cặp `(event_id=1, email='demo@example.test')`).
- **Kiểm tra luồng Web (`/register.php` → `/confirm.php` → `/complete.php`) và khởi động lại máy chủ PHP:**
  - Đăng ký sự kiện 1 với `webuser@example.test` → `CREATED`.
  - Đăng ký lại sự kiện 1 với `WEBUSER@example.test` (khác hoa/thường) → `DUPLICATE`.
  - Đăng ký sự kiện 2 với `webuser@example.test` → `CREATED`.
  - Dừng máy chủ PHP và truy vấn lại bằng SQL:
    ```sql
    SELECT id, event_id, name, email FROM registrations ORDER BY id;
    -- +----+----------+--------------+----------------------+
    -- | id | event_id | name         | email                |
    -- +----+----------+--------------+----------------------+
    -- |  1 |        1 | O'Neil Demo  | demo@example.test    |
    -- | 17 |        1 | Nguyễn Văn A | webuser@example.test |
    -- | 19 |        2 | Nguyễn Văn A | webuser@example.test |
    -- +----+----------+--------------+----------------------+

    SELECT event_id, email, COUNT(*) AS total FROM registrations GROUP BY event_id, email;
    -- +----------+----------------------+-------+
    -- | event_id | email                | total |
    -- +----------+----------------------+-------+
    -- |        1 | demo@example.test    |     1 |
    -- |        1 | webuser@example.test |     1 |
    -- |        2 | webuser@example.test |     1 |
    -- +----------+----------------------+-------+
    ```

### 3. Kết quả chạy `check`, `test` và `extend`

- **`bash lab.sh check`:**
  ```text
  CSDL=communitypass_mysql_v5 | USER=communitypass_mysql_v5@127.0.0.1
  MOI_TRUONG=SAN_SANG | Lab 5
  ```
- **`bash lab.sh test`:**
  - Kiểm thử Session (`tests/run.php`): `TESTS=6 FAILED=0`
  - Kiểm thử tích hợp CSDL (`tests/integration.php`): `TESTS=7 FAILED=0`
  - Kiểm thử ràng buộc MySQL (`tests/constraints.php`): `TESTS=9 FAILED=0`
- **`bash lab.sh extend`:**
  - **Trước khi hoàn thiện TODO:** `TESTS=4 FAILED=3` (chỉ đạt ca `missing` do hàm mặc định trả `[]`; 3 ca `zero-included`, `one`, `duplicate-no-change` chưa đạt vì chưa truy vấn sự kiện và đếm số đăng ký).
  - **Sau khi hoàn thiện `src/RegistrationStats.php`:** `TESTS=4 FAILED=0` (đạt cả 4 ca: `zero-included`, `one`, `duplicate-no-change`, `missing`).

### 4. Giải thích phần tự làm và phân tích nâng cao

- **Giải thích `registrationStats()` trong `src/RegistrationStats.php`:**
  - Dùng `FROM events e LEFT JOIN registrations r ON r.event_id = e.id WHERE e.id = ? GROUP BY e.id, e.title` với tham số `?` qua `queryParams()` để giữ lại hàng sự kiện ngay cả khi chưa có bản ghi nào bên bảng `registrations`.
  - Dùng `COUNT(r.id)` thay vì `COUNT(*)`: khi sự kiện chưa có đăng ký, `r.id` là `NULL` nên `COUNT(r.id)` trả về `0` (trong khi `COUNT(*)` sẽ đếm dòng kết quả của `LEFT JOIN` và trả về `1`).
  - Khi `fetch_assoc()` trả về `null` (ID sự kiện không tồn tại), hàm trả về `[]`. Khi có hàng, ép kiểu tường minh `(int)$row['event_id']`, `(string)$row['title']`, `(int)$row['total']`.
  - Không bắt và nuốt ngoại lệ `RuntimeException` từ `queryParams()`, đảm bảo lỗi kết nối/truy vấn CSDL không bị biến thành kết quả `0`.
- **Phân tích giới hạn chỗ khi nhiều request đăng ký đồng thời:**
  - Nếu chỉ đọc `COUNT(r.id) < capacity` bằng `SELECT` thông thường trước khi `INSERT`, hai request đồng thời khi còn 1 chỗ trống đều thấy `registered = capacity - 1` và cùng `INSERT` thành công (vượt quá `capacity` — lỗi race condition / TOCTOU).
  - Cách khắc phục trong MySQL (InnoDB): bọc thao tác kiểm tra và chèn trong một transaction (`BEGIN ... COMMIT`), khóa hàng sự kiện bằng `SELECT capacity FROM events WHERE id = ? FOR UPDATE` để tuần tự hóa các giao dịch đăng ký cùng một sự kiện, sau đó đếm số lượng hiện tại (`SELECT COUNT(id) FROM registrations WHERE event_id = ?`), nếu chưa đầy mới `INSERT`, ngược lại `ROLLBACK` và trả trạng thái hết chỗ.

