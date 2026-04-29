import { jsPDF } from "jspdf";
import html2canvas from "html2canvas";

export const generateMemoPdf = async (elementId: string) => {
    const element = document.getElementById(elementId);
    if (!element) return;

    element.classList.remove('hidden');
    try {
        const canvas = await html2canvas(element.querySelector('.memo-paper') as HTMLElement, {
            scale: 2,
            useCORS: true,
            logging: false
        });
        
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('p', 'mm', 'letter');
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        
        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
        window.open(pdf.output('bloburl'), '_blank');
    } finally {
        element.classList.add('hidden');
    }
};
