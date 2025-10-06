// resources/js/Composables/useSwal.ts
import Swal from 'sweetalert2';

export const useSwal = () => {
    return Swal.mixin({
        customClass: {
            confirmButton: 'bg-green-600 text-white px-4 py-2 rounded mr-2 hover:bg-green-700 focus:outline-none',
            cancelButton: 'bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 focus:outline-none',
        },
        buttonsStyling: false,
    });
};