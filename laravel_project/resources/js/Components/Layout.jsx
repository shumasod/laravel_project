import { Link } from '@inertiajs/react';

export default function Layout({ children }) {
    return (
        <div className="min-h-screen bg-gray-50">
            <nav className="bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <Link href="/" className="text-white text-xl font-bold hover:text-blue-200 transition">
                                宿泊管理システム
                            </Link>
                        </div>
                        <div className="flex items-center space-x-4">
                            <Link
                                href="/accommodations"
                                className="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            >
                                宿泊施設
                            </Link>
                            <Link
                                href="/rooms"
                                className="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            >
                                部屋
                            </Link>
                            <Link
                                href="/customers"
                                className="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            >
                                顧客
                            </Link>
                            <Link
                                href="/reservations"
                                className="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            >
                                予約
                            </Link>
                            <Link
                                href="/payments"
                                className="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            >
                                決済
                            </Link>
                            <Link
                                href="/reviews"
                                className="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            >
                                レビュー
                            </Link>
                            <Link
                                href="/reports/dashboard"
                                className="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium transition"
                            >
                                レポート
                            </Link>
                        </div>
                    </div>
                </div>
            </nav>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {children}
            </main>
        </div>
    );
}
