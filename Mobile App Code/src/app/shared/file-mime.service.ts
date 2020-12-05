import {Injectable} from '@angular/core';

@Injectable({
    providedIn: 'root'
})
export class FileMime {

    /**
     * Map of mime file type to material design icon name.
     */
    private iconsMap = {
        text: 'insert-drive-file',
        audio: 'volume-up',
        video: 'videocam',
        document: 'assignment',
        image: 'image'
    };

    /**
     * Return file type based on specified mime.
     */
    public getFileType(mime: string): string {
        return mime.split('/')[0];
    }

    /**
     * Return icon name based on specified mime.
     */
    public getIconName(mime: string): string {
        const type = this.getFileType(mime);
        return this.iconsMap[type] || this.iconsMap['text'];
    }
}
