import Layout from '@/Components/Layout';
import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Show({ review }) {
    const [showAdminResponse, setShowAdminResponse] = useState(false);
    const { data, setData, post, processing } = useForm({
        admin_response: '',
    });

    const handleAdminResponse = (e) => {
        e.preventDefault();
        post(`/reviews/${review.id}/admin-response`, {
            onSuccess: () => setShowAdminResponse(false),
        });
    };

    const renderStars = (rating, label) => (
        <div className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <span className="text-gray-700 font-medium">{label}</span>
            <div className="flex items-center">
                {[...Array(5)].map((_, i) => (
                    <svg
                        key={i}
                        className={`w-6 h-6 ${i < rating ? 'text-yellow-400' : 'text-gray-300'}`}
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                ))}
                <span className="ml-3 text-xl font-bold text-gray-900">{rating}</span>
            </div>
        </div>
    );

    return (
        <Layout>
            <div className="space-y-6">
                <div>
                    <Link href="/reviews" className="text-blue-600 hover:text-blue-800 font-medium">
                        ← 一覧に戻る
                    </Link>
                </div>

                <div className="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div className="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-6">
                        <div className="flex justify-between items-start">
                            <div>
                                <h1 className="text-2xl font-bold text-white mb-2">
                                    {review.title || `レビュー #${review.id}`}
                                </h1>
                                <p className="text-purple-100">
                                    {review.created_at} - {review.customer.name}
                                </p>
                            </div>
                            <div className="flex gap-2">
                                {review.is_published ? (
                                    <span className="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                                        公開
                                    </span>
                                ) : (
                                    <span className="px-4 py-2 bg-gray-100 text-gray-800 rounded-full font-semibold">
                                        非公開
                                    </span>
                                )}
                                {review.is_verified && (
                                    <span className="px-4 py-2 bg-blue-100 text-blue-800 rounded-full font-semibold">
                                        ✓ 認証済み
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="p-6 space-y-6">
                        <div className="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                            <h3 className="font-semibold text-blue-900 mb-2">宿泊情報</h3>
                            <div className="text-sm text-blue-800 space-y-1">
                                <p><strong>宿泊施設:</strong> {review.accommodation.name}</p>
                                <p>
                                    <strong>予約ID:</strong>{' '}
                                    <Link
                                        href={`/reservations/${review.reservation.id}`}
                                        className="text-blue-600 hover:underline"
                                    >
                                        #{review.reservation.id}
                                    </Link>
                                </p>
                            </div>
                        </div>

                        <div>
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">評価</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div className="col-span-full">
                                    <div className="flex items-center justify-center p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg border-2 border-yellow-200">
                                        <div className="text-center">
                                            <div className="text-6xl font-bold text-yellow-600 mb-2">
                                                {review.overall_rating}
                                            </div>
                                            <p className="text-gray-700 font-medium">総合評価</p>
                                        </div>
                                    </div>
                                </div>
                                {review.cleanliness_rating && renderStars(review.cleanliness_rating, '清潔さ')}
                                {review.service_rating && renderStars(review.service_rating, 'サービス')}
                                {review.location_rating && renderStars(review.location_rating, '立地')}
                                {review.value_rating && renderStars(review.value_rating, '価格')}
                                {review.amenities_rating && renderStars(review.amenities_rating, '設備')}
                            </div>
                        </div>

                        {review.comment && (
                            <div>
                                <h3 className="text-lg font-semibold text-gray-900 mb-3">コメント</h3>
                                <div className="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <p className="text-gray-800 leading-relaxed">{review.comment}</p>
                                </div>
                            </div>
                        )}

                        <div className="flex items-center gap-4 p-4 bg-blue-50 rounded-lg">
                            <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                            </svg>
                            <div>
                                <span className="text-2xl font-bold text-blue-600">{review.helpful_count}</span>
                                <span className="ml-2 text-gray-700">人が「役に立った」と評価</span>
                            </div>
                        </div>

                        {review.admin_response ? (
                            <div className="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                                <div className="flex items-start gap-3 mb-3">
                                    <svg className="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <div className="flex-1">
                                        <h4 className="font-semibold text-blue-900 mb-1">施設からの返信</h4>
                                        <p className="text-sm text-blue-700 mb-3">{review.admin_responded_at}</p>
                                        <p className="text-blue-900 leading-relaxed">{review.admin_response}</p>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div>
                                <button
                                    onClick={() => setShowAdminResponse(!showAdminResponse)}
                                    className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                                >
                                    施設から返信する
                                </button>

                                {showAdminResponse && (
                                    <div className="mt-4 bg-gray-50 rounded-lg p-6 border border-gray-200">
                                        <h4 className="font-semibold text-gray-900 mb-3">施設からの返信</h4>
                                        <form onSubmit={handleAdminResponse} className="space-y-4">
                                            <textarea
                                                value={data.admin_response}
                                                onChange={(e) => setData('admin_response', e.target.value)}
                                                rows="4"
                                                required
                                                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="返信内容を入力してください"
                                            />
                                            <div className="flex gap-3">
                                                <button
                                                    type="submit"
                                                    disabled={processing}
                                                    className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium transition"
                                                >
                                                    {processing ? '送信中...' : '返信を投稿'}
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => setShowAdminResponse(false)}
                                                    className="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition"
                                                >
                                                    キャンセル
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                )}
                            </div>
                        )}

                        <div className="flex gap-3 pt-4 border-t">
                            <Link
                                href={`/reviews/${review.id}/edit`}
                                className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition"
                            >
                                編集
                            </Link>
                            <Link
                                href={`/reviews/${review.id}`}
                                method="delete"
                                as="button"
                                className="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-medium transition"
                            >
                                削除
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
}
