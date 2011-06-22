function loadTxt()
    {
    document.getElementById("txtLang").innerHTML = "Wrap Text";
    document.getElementById("btnCancel").value = "Cancel";
    document.getElementById("btnApply").value = "Apply";
    document.getElementById("btnOk").value = " OK ";
    }
function getTxt(s)
    {
    switch(s)
        {
        case "Search":return "Search";
        case "Cut":return "Cut";
        case "Copy":return "Copy";
        case "Paste":return "Paste";
        case "Undo":return "Undo";
        case "Redo":return "Redo";
        default:return "";
        }
    }
function writeTitle()
    {
    document.write("<title>Source Editor</title>")
    }
