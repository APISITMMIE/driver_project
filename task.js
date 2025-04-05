let isEnglish = false;

        // ดึงข้อมูลมาใส่ในตาราง
        window.onload = function() {
            const taskList = JSON.parse(localStorage.getItem("taskList")) || [];
            const taskListBody = document.getElementById("taskListBody");

            taskList.forEach((task) => {
                const row = document.createElement("tr");

                row.onclick = function() {
                    openModal(task);
                };

                let totalDistance = '-';
                if (task.mileageAtDestination && task.mileage) {
                    totalDistance = task.mileageAtDestination - task.mileage;
                }

                row.innerHTML = `
                    <td>${task.date}</td>
                    <td>${task.location}</td>
                    <td>${task.startTime}</td>
                    <td>${task.mileage}</td>
                    <td>${task.destinationLocation || '-'}</td>
                    <td>${task.destinationTime || '-'}</td>
                    <td>${task.mileageAtDestination || '-'}</td>
                    <td>${totalDistance}</td>
                    <td>${task.driverName}</td>
                `;
                taskListBody.appendChild(row);
            });
        };

        // Function เปิด pop-up
        function openModal(task) {
            const modal = document.getElementById("myModal");
            const modalBody = document.getElementById("modalBody");
            const modalHeader = document.querySelector(".modal-header h2");

            if (isEnglish) {
                modalHeader.innerHTML = `Travel Details (Date: ${task.date})`;
            } else {
                modalHeader.innerHTML = `รายละเอียดการเดินทาง (วันที่: ${task.date})`;
            }

            modalBody.innerHTML = `
                <p><strong>${isEnglish ? 'Driver\'s Name' : 'ชื่อคนขับรถ'}:</strong> ${task.driverName}</p>
                <p><strong>${isEnglish ? 'Start Time' : 'เวลาเริ่มเดินทาง'}:</strong> ${task.startTime}</p>
                <p><strong>${isEnglish ? 'Starting Location' : 'สถานที่ต้นทาง'}:</strong> ${task.location}</p>
                <p><strong>${isEnglish ? 'Mileage at Start' : 'เลขไมล์ตอนเริ่มเดินทาง'}:</strong> ${task.mileage}</p>
                <p><strong>${isEnglish ? 'Arrival Time' : 'เวลาเมื่อถึงปลายทาง'}:</strong> ${task.destinationTime || '-'}</p>
                <p><strong>${isEnglish ? 'Destination Location' : 'สถานที่ปลายทาง'}:</strong> ${task.destinationLocation || '-'}</p>
                <p><strong>${isEnglish ? 'Mileage at Destination' : 'เลขไมล์เมื่อถึงที่หมาย'}:</strong> ${task.mileageAtDestination || '-'}</p>
                <p><strong>${isEnglish ? 'Private/Official' : 'Private/Official'}:</strong> ${task.accessories.join(', ') || '-'}</p>
                <p><strong>${isEnglish ? 'Purpose of Trip' : 'จุดประสงค์ในการเดินทาง'}:</strong> ${task.tripTypes.join(', ') || '-'}</p><br>
            `;

            if (task.imageUrl) {
                modalBody.innerHTML += `<p><strong>${isEnglish ? 'Start Mileage Image' : 'รูปภาพเลขไมล์ต้นทาง'}<br><img src="${task.imageUrl}" alt="Start Mileage Image" style="width: 100%; max-width: 250px; margin-top: 20px; border-radius: 10px;"></p>`;
            }

            if (task.destinationImageUrl) {
                modalBody.innerHTML += `<p><strong>${isEnglish ? 'Destination Mileage Image' : 'รูปภาพเลขไมล์ปลายทาง'}<br><img src="${task.destinationImageUrl}" alt="Destination Mileage Image" style="width: 100%; max-width: 250px; margin-top: 20px; border-radius: 10px;"></p>`;
            }

            modal.style.display = "block";
        }

        // Function ปิด pop-up
        function closeModal() {
            const modal = document.getElementById("myModal");
            modal.style.display = "none";
        }

        document.querySelector(".close").onclick = function() {
            closeModal();
        };

        window.onclick = function(event) {
            const modal = document.getElementById("myModal");
            if (event.target == modal) {
                closeModal();
            }
        };

        // Translation map
        const translationMap = {
            "รายละเอียดการเดินทาง": "Travel Details",
            "ชื่อคนขับรถ": "Driver's Name",
            "เวลาเริ่มเดินทาง": "Start Time",
            "สถานที่ต้นทาง": "Starting Location",
            "เลขไมล์ตอนเริ่มเดินทาง": "Mileage at Start",
            "เวลาเมื่อถึงปลายทาง": "Arrival Time",
            "สถานที่ปลายทาง": "Destination Location",
            "เลขไมล์เมื่อถึงที่หมาย": "Mileage at Destination",
            "Private/Official": "Private/Official",
            "จุดประสงค์ในการเดินทาง": "Purpose of Trip",
            "รูปภาพเลขไมล์ต้นทาง": "Start Mileage Image",
            "รูปภาพเลขไมล์ปลายทาง": "Destination Mileage Image",
            "บันทึกข้อมูลเรียบร้อย": "Information Saved Successfully",
            "กรุณากรอกข้อมูลให้ครบถ้วน": "Please complete all fields"
        };

        function toggleLanguage() {
            if (isEnglish) {
                document.querySelector('h1').innerText = "Task List";
                document.querySelector('th:nth-child(1)').innerText = "วันที่";
                document.querySelector('th:nth-child(2)').innerText = "สถานที่ต้นทาง";
                document.querySelector('th:nth-child(3)').innerText = "เวลาเริ่มเดินทาง";
                document.querySelector('th:nth-child(4)').innerText = "เลขไมล์ก่อนเดินทาง";
                document.querySelector('th:nth-child(5)').innerText = "สถานที่ปลายทาง";
                document.querySelector('th:nth-child(6)').innerText = "เวลาปลายทาง";
                document.querySelector('th:nth-child(7)').innerText = "เลขไมล์เมื่อถึงที่หมาย";
                document.querySelector('th:nth-child(8)').innerText = "รวมระยะทาง";
                document.querySelector('th:nth-child(9)').innerText = "ชื่อพนักงานขับรถ";
                isEnglish = false;
                document.querySelector(".translate-button-container button").innerText = "English";
            } else {
                translateToEnglish();
                isEnglish = true;
                document.querySelector(".translate-button-container button").innerText = "ภาษาไทย";
            }
        }

        // Function to translate content to English
        function translateToEnglish() {
            const elements = document.querySelectorAll('[data-translate]'); 
            elements.forEach(el => {
                const thaiText = el.innerText || el.textContent;
                const translatedText = translationMap[thaiText] || thaiText; 
                el.innerText = translatedText; 
            });

            document.querySelector('h1').innerText = "Task List";
            document.querySelector('th:nth-child(1)').innerText = "Date";
            document.querySelector('th:nth-child(2)').innerText = "Starting Location";
            document.querySelector('th:nth-child(3)').innerText = "Start Time";
            document.querySelector('th:nth-child(4)').innerText = "Mileage at Start";
            document.querySelector('th:nth-child(5)').innerText = "Destination Location";
            document.querySelector('th:nth-child(6)').innerText = "Arrival Time";
            document.querySelector('th:nth-child(7)').innerText = "Mileage at Destination";
            document.querySelector('th:nth-child(8)').innerText = "Total Distance";
            document.querySelector('th:nth-child(9)').innerText = "Driver's Name";
        }
        // End translate
