// Save selected answer
function saveAnswer(questionId, selectedOption) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "save_progress.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.querySelectorAll(".option").forEach(option => option.classList.remove("selected"));
            document.getElementById("option-" + selectedOption).classList.add("selected");
        }
    };
    xhr.send(`question_id=${questionId}&selected_answer=${selectedOption}`);
}

// Save checkbox state
function saveCheckbox(questionId, columnName, isChecked) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "save_progress.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            console.log(`Checkbox ${columnName} updated successfully.`);
        }
    };
    xhr.send(`question_id=${questionId}&column_name=${columnName}&is_checked=${isChecked ? 1 : 0}`);
}