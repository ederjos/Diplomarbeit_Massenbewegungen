import { latLng } from 'leaflet';

export function distanceTo(lat1: number, lon1: number, lat2: number, lon2: number): number {
    const p1 = latLng(lat1, lon1);
    const p2 = latLng(lat2, lon2);
    return p1.distanceTo(p2);
}
